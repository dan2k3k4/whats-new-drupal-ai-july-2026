<?php

namespace Drupal\node_exclude_by_region\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Subscribes to alter the node routes with access control based on region.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection): void {
    if ($route = $collection->get('entity.node.canonical')) {
      $route->setRequirement('_custom_access', '\Drupal\node_exclude_by_region\NodeRegionAccessCheck::access');
    }
  }

}
