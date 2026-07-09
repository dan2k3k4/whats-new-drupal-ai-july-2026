<?php

namespace Drupal\node_exclude_by_region;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityHandlerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\NodeAccessControlHandler as BaseNodeAccessControlHandler;
use Drupal\node\NodeAccessControlHandlerInterface;
use Drupal\node\NodeInterface;
use Drupal\node_exclude_by_region\Utility\NodeRegionAccess;

/**
 * Access controller for Node entity.
 */
class NodeAccessControlHandler extends BaseNodeAccessControlHandler implements NodeAccessControlHandlerInterface, EntityHandlerInterface {

  protected function checkAccess(EntityInterface $node, $operation, AccountInterface $account) {
    $access = parent::checkAccess($node, $operation, $account);

    if ($operation === 'view') {
      if ($node instanceof NodeInterface) {
        if (!NodeRegionAccess::canAccess($node)) {
          return AccessResult::forbidden();
        }
      }
    }

    return $access;
  }

}
