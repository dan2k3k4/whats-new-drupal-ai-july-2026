<?php

/**
 * @file
 * Creates the AI Search server + index over sessions and speakers.
 * Run: drush php:script scripts/05-search-index.php
 */

use Drupal\search_api\Entity\Server;
use Drupal\search_api\Entity\Index;

if (!Server::load('driftwood_vdb')) {
  Server::create([
    'id' => 'driftwood_vdb',
    'name' => 'Driftwood vector DB (amazee.ai)',
    'backend' => 'search_api_ai_search',
    'backend_config' => [
      'database' => 'amazeeio_vector_db',
      'database_settings' => [
        'database_name' => 'db_1a3d35ee',
        'collection' => 'driftwood_camp',
        'metric' => 'cosine_similarity',
      ],
      'embeddings_engine' => 'amazeeio__titan-embed-text-v2:0',
      'embeddings_engine_configuration' => [
        'set_dimensions' => FALSE,
        'dimensions' => 1024,
      ],
      'embedding_strategy' => 'contextual_chunks',
      'embedding_strategy_configuration' => [
        'chunk_size' => '500',
        'chunk_min_overlap' => '100',
        'contextual_content_max_percentage' => '30',
      ],
    ],
  ])->save();
  echo "Server created\n";
}

if (!Index::load('driftwood_content')) {
  $fields = [
    'title' => ['type' => 'text', 'propPath' => 'title', 'option' => 'contextual_content'],
    'body' => ['type' => 'text', 'propPath' => 'body', 'option' => 'main_content'],
    'field_speakers' => ['type' => 'text', 'propPath' => 'field_speakers:entity:title', 'option' => 'contextual_content'],
    'field_track' => ['type' => 'text', 'propPath' => 'field_track:entity:name', 'option' => 'contextual_content'],
    'field_level' => ['type' => 'text', 'propPath' => 'field_level:entity:name', 'option' => 'contextual_content'],
    'field_length' => ['type' => 'text', 'propPath' => 'field_length:entity:name', 'option' => 'contextual_content'],
    'field_room' => ['type' => 'string', 'propPath' => 'field_room', 'option' => 'contextual_content'],
    'field_organisation' => ['type' => 'text', 'propPath' => 'field_organisation', 'option' => 'contextual_content'],
    'field_role' => ['type' => 'text', 'propPath' => 'field_role', 'option' => 'contextual_content'],
  ];
  $index = Index::create([
    'id' => 'driftwood_content',
    'name' => 'Driftwood camp content',
    'server' => 'driftwood_vdb',
    'datasource_settings' => [
      'entity:node' => [
        'bundles' => ['default' => FALSE, 'selected' => ['session', 'speaker']],
      ],
    ],
    'options' => ['index_directly' => TRUE, 'cron_limit' => 50],
  ]);
  foreach ($fields as $id => $def) {
    $field = \Drupal::getContainer()->get('search_api.fields_helper')->createField($index, $id, [
      'label' => $id,
      'type' => $def['type'],
      'datasource_id' => 'entity:node',
      'property_path' => $def['propPath'],
    ]);
    $index->addField($field);
  }
  $index->save();
  echo "Index created\n";

  // Per-field indexing options for the embedding strategy.
  \Drupal::configFactory()->getEditable('ai_search.index.driftwood_content')
    ->set('index_id', 'driftwood_content')
    ->set('control_field_max_length', FALSE)
    ->set('exclude_chunk_from_metadata', FALSE)
    ->set('indexing_options', array_map(
      fn($def) => ['indexing_option' => $def['option']],
      $fields,
    ))
    ->save();
  echo "Indexing options saved\n";
}
