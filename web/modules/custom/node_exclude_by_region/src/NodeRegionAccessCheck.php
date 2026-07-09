<?php

namespace Drupal\node_exclude_by_region;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultAllowed;
use Drupal\Core\Access\AccessResultForbidden;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\NodeInterface;
use Drupal\node_exclude_by_region\Utility\NodeRegionAccess;
use Symfony\Component\Routing\Route;
use Drupal\Core\Routing\Access\AccessInterface;

/**
 * Access controller for Node entity.
 */
class NodeRegionAccessCheck implements AccessInterface {

  public function access(NodeInterface $node, AccountInterface $account, Route $route): AccessResultForbidden|AccessResultAllowed {
    if (!NodeRegionAccess::canAccess($node)) {
      return AccessResult::forbidden();
    }

    return AccessResult::allowed();
  }

}
