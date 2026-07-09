<?php

/**
 * @file
 * Creates the camp demo content model. Run: drush php:script scripts/01-content-model.php
 * Idempotent: skips anything that already exists.
 */

use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\taxonomy\Entity\Term;
use Drupal\node\Entity\NodeType;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;

$vocabs = [
  'session_length' => ['Session length', ['15 min lightning talk', '30 min talk', '45 min talk', 'Half-day workshop', 'Full-day workshop']],
  'track' => ['Track', ['AI & Machine Learning', 'DevOps & Hosting', 'Frontend & Design', 'Backend & APIs', 'Community & Open Source']],
  'level' => ['Experience level', ['Beginner', 'Intermediate', 'Expert']],
];

foreach ($vocabs as $vid => [$label, $terms]) {
  if (!Vocabulary::load($vid)) {
    Vocabulary::create(['vid' => $vid, 'name' => $label])->save();
    echo "Created vocab $vid\n";
  }
  $existing = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties(['vid' => $vid]);
  $names = array_map(fn($t) => $t->label(), $existing);
  foreach ($terms as $weight => $name) {
    if (!in_array($name, $names)) {
      Term::create(['vid' => $vid, 'name' => $name, 'weight' => $weight])->save();
      echo "  term: $name\n";
    }
  }
}

if (!FieldStorageConfig::loadByName('node', 'body')) {
  FieldStorageConfig::create([
    'field_name' => 'body',
    'entity_type' => 'node',
    'type' => 'text_with_summary',
  ])->save();
  echo "Created body field storage\n";
}

foreach (['speaker' => 'Speaker', 'session' => 'Session'] as $type => $label) {
  if (!NodeType::load($type)) {
    NodeType::create(['type' => $type, 'name' => $label, 'display_submitted' => FALSE])->save();
    echo "Created node type $type\n";
  }
  if (!FieldConfig::loadByName('node', $type, 'body')) {
    FieldConfig::create([
      'field_name' => 'body',
      'entity_type' => 'node',
      'bundle' => $type,
      'label' => $type === 'speaker' ? 'Bio' : 'Description',
      'settings' => ['display_summary' => TRUE, 'allowed_formats' => []],
    ])->save();
    echo "Added body to $type\n";
  }
}

// [entity_type, bundle, field_name, label, field_type, storage_settings, field_settings, cardinality]
$fields = [
  ['speaker', 'field_photo', 'Photo', 'image', [], [], 1],
  ['speaker', 'field_organisation', 'Organisation', 'string', [], [], 1],
  ['speaker', 'field_role', 'Role', 'string', [], [], 1],
  ['session', 'field_speakers', 'Speaker(s)', 'entity_reference', ['target_type' => 'node'], ['handler_settings' => ['target_bundles' => ['speaker' => 'speaker']]], -1],
  ['session', 'field_length', 'Session length', 'entity_reference', ['target_type' => 'taxonomy_term'], ['handler_settings' => ['target_bundles' => ['session_length' => 'session_length']]], 1],
  ['session', 'field_track', 'Track', 'entity_reference', ['target_type' => 'taxonomy_term'], ['handler_settings' => ['target_bundles' => ['track' => 'track']]], 1],
  ['session', 'field_level', 'Experience level', 'entity_reference', ['target_type' => 'taxonomy_term'], ['handler_settings' => ['target_bundles' => ['level' => 'level']]], 1],
  ['session', 'field_room', 'Room', 'string', [], [], 1],
  ['session', 'field_time_slot', 'Time slot', 'datetime', ['datetime_type' => 'datetime'], [], 1],
  ['session', 'field_summary_nontech', 'For non-techies', 'text_long', [], [], 1],
  ['session', 'field_summary_expert', 'For experts', 'text_long', [], [], 1],
];

foreach ($fields as [$bundle, $name, $label, $type, $storage_settings, $settings, $cardinality]) {
  if (!FieldStorageConfig::loadByName('node', $name)) {
    FieldStorageConfig::create([
      'field_name' => $name,
      'entity_type' => 'node',
      'type' => $type,
      'settings' => $storage_settings,
      'cardinality' => $cardinality,
    ])->save();
  }
  if (!FieldConfig::loadByName('node', $bundle, $name)) {
    FieldConfig::create([
      'field_name' => $name,
      'entity_type' => 'node',
      'bundle' => $bundle,
      'label' => $label,
      'settings' => $settings,
    ])->save();
    echo "Field $bundle.$name\n";
  }
}

// Form + view displays: sensible defaults.
$edr = \Drupal::service('entity_display.repository');
foreach (['speaker', 'session'] as $bundle) {
  $form = $edr->getFormDisplay('node', $bundle);
  $view = $edr->getViewDisplay('node', $bundle);
  $teaser = $edr->getViewDisplay('node', $bundle, 'teaser');
  $form->setComponent('body', ['type' => 'text_textarea_with_summary']);
  $view->setComponent('body', ['label' => 'hidden', 'type' => 'text_default', 'weight' => 0]);
  foreach ($fields as [$b, $name, $label, $type]) {
    if ($b !== $bundle) {
      continue;
    }
    $form->setComponent($name, []);
    $view->setComponent($name, $type === 'text_long' ? ['label' => 'above'] : ['label' => 'inline']);
  }
  if ($bundle === 'speaker') {
    $view->setComponent('field_photo', ['label' => 'hidden', 'type' => 'image', 'weight' => -10]);
  }
  if ($bundle === 'session') {
    $teaser->setComponent('field_track', ['label' => 'inline'])->setComponent('field_level', ['label' => 'inline'])->setComponent('field_length', ['label' => 'inline']);
    $teaser->save();
  }
  $form->save();
  $view->save();
}
echo "Displays configured.\n";
