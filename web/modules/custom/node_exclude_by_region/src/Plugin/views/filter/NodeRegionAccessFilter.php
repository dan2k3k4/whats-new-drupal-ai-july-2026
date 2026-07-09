<?php

namespace Drupal\node_exclude_by_region\Plugin\views\filter;

use Drupal\views\Annotation\ViewsFilter;
use Drupal\views\Plugin\views\filter\FilterPluginBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Filters nodes based on region access.
 *
 * @ViewsFilter("node_region_access_filter")
 */
class NodeRegionAccessFilter extends FilterPluginBase {

  /**
   * {@inheritdoc}
   */
  public function query(): void
  {
    // Add a condition to the query to filter nodes based on region access.
    $this->ensureMyTable();
    $current_region = "2"; // Replace with dynamic region ID if needed
    $this->query->addWhereExpression(0, "NOT EXISTS (SELECT 1 FROM {node_field_data} nfd WHERE nfd.nid = {$this->tableAlias}.nid AND nfd.status = 1 AND NOT EXISTS (SELECT 1 FROM {field_data_field_exclude_by_region} fefr WHERE fefr.entity_id = nfd.nid AND fefr.field_exclude_by_region_target_id = :current_region))", [':current_region' => $current_region]);
  }

  /**
   * {@inheritdoc}
   */
  public function buildExposeForm(&$form, FormStateInterface $form_state) {
    // No expose form needed.
  }

}
