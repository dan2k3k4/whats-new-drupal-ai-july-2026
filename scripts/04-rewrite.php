<?php

/**
 * @file
 * Runs the audience rewrite agents on session nodes.
 * The same generation also happens automatically on save for empty fields
 * (see driftwood_demo_node_presave()); this script exists for bulk runs.
 *
 * One node:  drush php:script scripts/04-rewrite.php -- 12
 * All nodes: drush php:script scripts/04-rewrite.php -- --all
 * Redo:      add --force to overwrite existing summaries.
 */

$args = $extra ?? [];
$force = in_array('--force', $args);
// CCC filters context items by view access; run as admin, not anonymous.
\Drupal::service('account_switcher')->switchTo(\Drupal\user\Entity\User::load(1));
$nodeStorage = \Drupal::entityTypeManager()->getStorage('node');

if (in_array('--all', $args)) {
  $nids = $nodeStorage->getQuery()->condition('type', 'session')->accessCheck(FALSE)->sort('nid')->execute();
}
else {
  $nids = array_filter($args, 'is_numeric');
  if (!$nids) {
    echo "Usage: drush php:script scripts/04-rewrite.php -- <nid>|--all [--force]\n";
    return;
  }
}

foreach ($nodeStorage->loadMultiple($nids) as $node) {
  if ($node->bundle() !== 'session') {
    continue;
  }
  echo $node->label() . "\n";
  if ($force) {
    foreach (DRIFTWOOD_DEMO_REWRITE_AGENTS as $field) {
      $node->set($field, NULL);
    }
  }
  // Presave fills any empty summary fields via the agents.
  $node->save();
  foreach (DRIFTWOOD_DEMO_REWRITE_AGENTS as $field) {
    $val = $node->get($field)->value;
    echo "  $field: " . ($val ? mb_substr(trim($val), 0, 90) . '…' : 'EMPTY') . "\n";
  }
}
echo "Done.\n";
