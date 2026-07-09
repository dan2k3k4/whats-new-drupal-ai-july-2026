<?php

namespace Drupal\node_exclude_by_region\Utility;

use Drupal\node\NodeInterface;

class NodeRegionAccess {

  public static function canAccess(NodeInterface $node): bool
  {
    if ($node->hasField('field_exclude_by_region') && !$node->get('field_exclude_by_region')->isEmpty()) {
      $exclude_by_region = $node->get('field_exclude_by_region')->getValue();

      $currentRegionTermId = "1";

      foreach ($exclude_by_region as $region) {
        if ($region['target_id'] === $currentRegionTermId) {
          return false;
        }
      }
    }

    return true;
  }
}
