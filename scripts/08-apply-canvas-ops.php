<?php

/**
 * @file
 * Applies a Canvas AI operations JSON to a canvas_page and publishes it.
 *
 * Usage:
 *   drush php:script scripts/08-apply-canvas-ops.php -- <ops.json> <title> <alias> [page_id]
 *
 * With page_id the existing page is overwritten; otherwise a page is created.
 */

use Drupal\canvas\Entity\Component;
use Drupal\canvas\Entity\Page;

[$ops_file, $title, $alias] = [$extra[0] ?? NULL, $extra[1] ?? NULL, $extra[2] ?? NULL];
$page_id = $extra[3] ?? NULL;
if (!$ops_file || !$title || !$alias) {
  echo "Usage: drush php:script scripts/08-apply-canvas-ops.php -- <ops.json> <title> <alias> [page_id]\n";
  return;
}

$response = json_decode(file_get_contents($ops_file), TRUE);
$components = [];
foreach ($response['operations'] ?? [] as $op) {
  foreach ($op['components'] as $c) {
    $component = Component::load($c['id']);
    if (!$component) {
      echo "SKIP unknown component {$c['id']}\n";
      continue;
    }
    $components[] = [
      'uuid' => \Drupal::service('uuid')->generate(),
      'component_id' => $c['id'],
      'component_version' => $component->getActiveVersion(),
      'inputs' => $c['fieldValues'],
      'label' => $component->label(),
    ];
  }
}
if (!$components) {
  echo "No components in $ops_file — aborting.\n";
  return;
}

$storage = \Drupal::entityTypeManager()->getStorage('canvas_page');
if ($page_id) {
  /** @var \Drupal\canvas\Entity\Page $page */
  $page = $storage->load($page_id);
  $page->set('title', $title);
  $page->set('components', $components);
  $page->set('path', ['alias' => $alias]);
  $page->set('status', TRUE);
}
else {
  $page = Page::create([
    'title' => $title,
    'status' => TRUE,
    'path' => ['alias' => $alias],
    'components' => $components,
  ]);
}
$violations = $page->validate();
if (count($violations)) {
  foreach ($violations as $v) {
    echo "VIOLATION: {$v->getPropertyPath()}: {$v->getMessage()}\n";
  }
  return;
}
$page->save();
echo "Published canvas_page {$page->id()} '{$title}' at {$alias} with " . count($components) . " components\n";
