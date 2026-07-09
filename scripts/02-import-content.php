<?php

/**
 * @file
 * Imports camp demo content. Run: drush php:script scripts/02-import-content.php
 * Idempotent: matches speakers/sessions by title.
 */

use Drupal\node\Entity\Node;
use Drupal\file\Entity\File;

$data = json_decode(file_get_contents(__DIR__ . '/camp-content.json'), TRUE);
$nodeStorage = \Drupal::entityTypeManager()->getStorage('node');
$termStorage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');

$termId = function (string $vid, string $name) use ($termStorage) {
  $terms = $termStorage->loadByProperties(['vid' => $vid, 'name' => $name]);
  if (!$terms) {
    throw new \RuntimeException("Missing term $vid:$name");
  }
  return reset($terms)->id();
};

// ponytail: GD initial-avatars stand in for real headshots; swap the files in
// public://speaker-photos/ if you get proper images.
$makeAvatar = function (string $name, string $slug): File {
  $dir = 'public://speaker-photos';
  \Drupal::service('file_system')->prepareDirectory($dir, \Drupal\Core\File\FileSystemInterface::CREATE_DIRECTORY);
  $uri = "$dir/$slug.png";
  $palette = [[38, 70, 83], [42, 157, 143], [233, 196, 106], [244, 162, 97], [231, 111, 81], [96, 108, 56], [40, 54, 24], [69, 123, 157], [29, 53, 87], [128, 128, 0]];
  [$r, $g, $b] = $palette[crc32($slug) % count($palette)];
  $im = imagecreatetruecolor(400, 400);
  imagefill($im, 0, 0, imagecolorallocate($im, $r, $g, $b));
  $initials = implode('', array_map(fn($w) => mb_strtoupper(mb_substr($w, 0, 1)), array_slice(preg_split('/[\s-]+/', $name), 0, 2)));
  $white = imagecolorallocate($im, 255, 255, 255);
  // Built-in font 5 scaled up: draw on small canvas, upscale.
  $small = imagecreatetruecolor(100, 100);
  imagefill($small, 0, 0, imagecolorallocate($small, $r, $g, $b));
  $w = imagefontwidth(5) * strlen($initials);
  imagestring($small, 5, (int) ((100 - $w) / 2), 42, $initials, imagecolorallocate($small, 255, 255, 255));
  imagecopyresampled($im, $small, 0, 0, 0, 0, 400, 400, 100, 100);
  imagedestroy($small);
  ob_start();
  imagepng($im);
  $png = ob_get_clean();
  imagedestroy($im);
  \Drupal::service('file.repository')->writeData($png, $uri, \Drupal\Core\File\FileExists::Replace);
  $files = \Drupal::entityTypeManager()->getStorage('file')->loadByProperties(['uri' => $uri]);
  if ($files) {
    return reset($files);
  }
  $file = File::create(['uri' => $uri, 'status' => 1]);
  $file->save();
  return $file;
};

$speakerIds = [];
foreach ($data['speakers'] as $slug => $s) {
  $existing = $nodeStorage->loadByProperties(['type' => 'speaker', 'title' => $s['name']]);
  if ($existing) {
    $speakerIds[$slug] = reset($existing)->id();
    continue;
  }
  $file = $makeAvatar($s['name'], $slug);
  $node = Node::create([
    'type' => 'speaker',
    'title' => $s['name'],
    'body' => ['value' => $s['bio'], 'format' => 'basic_html'],
    'field_organisation' => $s['org'],
    'field_role' => $s['role'],
    'field_photo' => ['target_id' => $file->id(), 'alt' => 'Portrait of ' . $s['name']],
    'status' => 1,
  ]);
  $node->save();
  $speakerIds[$slug] = $node->id();
  echo "Speaker: {$s['name']}\n";
}

foreach ($data['sessions'] as $s) {
  if ($nodeStorage->loadByProperties(['type' => 'session', 'title' => $s['title']])) {
    continue;
  }
  $node = Node::create([
    'type' => 'session',
    'title' => $s['title'],
    'body' => ['value' => $s['description'], 'format' => 'basic_html'],
    'field_speakers' => array_map(fn($slug) => ['target_id' => $speakerIds[$slug]], $s['speakers']),
    'field_track' => $termId('track', $s['track']),
    'field_level' => $termId('level', $s['level']),
    'field_length' => $termId('session_length', $s['length']),
    'field_room' => $s['room'],
    'field_time_slot' => $s['time'],
    'status' => 1,
  ]);
  $node->save();
  echo "Session: {$s['title']}\n";
}
echo "Done. Speakers: " . count($speakerIds) . ", sessions: " . count($data['sessions']) . "\n";
