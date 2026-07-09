<?php

/**
 * @file
 * Restores pre-generated audience summaries from camp-summaries.json,
 * so a rebuild does not need ~80 LLM calls.
 * Run: drush php:script scripts/07-restore-summaries.php
 */

$data = json_decode(file_get_contents(__DIR__ . '/camp-summaries.json'), TRUE);
$storage = \Drupal::entityTypeManager()->getStorage('node');
$restored = 0;
foreach ($data as $title => $summaries) {
  $nodes = $storage->loadByProperties(['type' => 'session', 'title' => $title]);
  if (!$nodes) {
    echo "MISSING: $title\n";
    continue;
  }
  $node = reset($nodes);
  $node->set('field_summary_nontech', ['value' => $summaries['nontech'], 'format' => 'basic_html']);
  $node->set('field_summary_expert', ['value' => $summaries['expert'], 'format' => 'basic_html']);
  $node->save();
  $restored++;
}
echo "Restored summaries on $restored sessions.\n";
