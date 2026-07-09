<?php

/**
 * @file
 * Creates the camp RAG assistant (agent + assistant + chatbot block) and the
 * /search view. Run: drush php:script scripts/06-assistant-and-view.php
 */

use Drupal\ai_agents\Entity\AiAgent;
use Drupal\ai_assistant_api\Entity\AiAssistant;
use Drupal\block\Entity\Block;
use Drupal\views\Entity\View;

if (!AiAgent::load('camp_assistant')) {
  AiAgent::create([
    'id' => 'camp_assistant',
    'label' => 'Camp programme assistant',
    'description' => 'Answers questions about the Driftwood TechCamp programme using the vector index.',
    'system_prompt' => <<<EOT
You are the friendly programme assistant for Driftwood TechCamp 2026, a three-day lakeside tech camp (16-18 September 2026) with talks and workshops on AI, DevOps, frontend, backend and open source community topics.

Always use the rag_search tool to look up sessions and speakers before answering. Base answers only on what the search returns — never invent sessions, times or speakers. Mention session titles, rooms and times when relevant. If the search returns nothing useful, say so and suggest rephrasing.

Keep answers short, warm and practical, like a helpful camp counsellor.
EOT,
    'secured_system_prompt' => '[ai_agent:agent_instructions]',
    'tools' => ['ai_search:rag_search' => TRUE],
    'tool_usage_limits' => [
      'ai_search:rag_search' => [
        'index' => ['values' => ['driftwood_content'], 'action' => 'force_value', 'hide_property' => 1],
        'amount' => ['values' => [8], 'action' => 'force_value', 'hide_property' => 1],
        'min_score' => ['values' => [0.2], 'action' => 'force_value', 'hide_property' => 1],
      ],
    ],
    'orchestration_agent' => TRUE,
    'triage_agent' => FALSE,
    'max_loops' => 3,
    'masquerade_roles' => [],
    'exclude_users_role' => FALSE,
    'structured_output_enabled' => FALSE,
    'structured_output_schema' => '',
    'guardrail_set' => '',
    'hostname_filter_disabled' => FALSE,
  ])->save();
  echo "Agent camp_assistant\n";
}

if (!AiAssistant::load('camp_assistant')) {
  AiAssistant::create([
    'id' => 'camp_assistant',
    'label' => 'Camp programme assistant',
    'description' => 'RAG chatbot over the camp programme.',
    'allow_history' => 'session',
    'history_context_length' => 2,
    'pre_action_prompt' => '',
    'system_prompt' => '',
    'instructions' => '',
    'error_message' => 'Sorry, something went wrong — try asking again.',
    'llm_provider' => 'amazeeio',
    'llm_model' => 'claude-4-6-sonnet',
    'llm_configuration' => [],
    'use_function_calling' => TRUE,
    'ai_agent' => 'camp_assistant',
    'roles' => [],
  ])->save();
  echo "Assistant camp_assistant\n";
}

if (!Block::load('campassistantchatbot')) {
  Block::create([
    'id' => 'campassistantchatbot',
    'theme' => 'olivero',
    'region' => 'content_below',
    'plugin' => 'ai_chatbot_block',
    'weight' => 10,
    'settings' => [
      'id' => 'ai_chatbot_block',
      'label' => 'Ask the camp assistant',
      'label_display' => '0',
      'provider' => 'ai_chatbot',
      'ai_assistant' => 'camp_assistant',
      'bot_name' => 'Camp Assistant',
      'bot_image' => '/core/misc/druplicon.png',
      'use_username' => FALSE,
      'default_username' => 'Camper',
      'use_avatar' => FALSE,
      'default_avatar' => '/core/misc/favicon.ico',
      'first_message' => 'Hi camper! Ask me anything about the Driftwood TechCamp programme.',
      'stream' => TRUE,
      'toggle_state' => 'remember',
      'output_type' => 'markdown',
      'show_structured_results' => FALSE,
    ],
    'visibility' => [],
  ])->save();
  echo "Chatbot block placed\n";
}

if (!View::load('camp_search')) {
  View::create([
    'id' => 'camp_search',
    'label' => 'Camp search',
    'description' => 'Semantic search over the camp programme.',
    'base_table' => 'search_api_index_driftwood_content',
    'display' => [
      'default' => [
        'display_plugin' => 'default',
        'id' => 'default',
        'display_title' => 'Default',
        'position' => 0,
        'display_options' => [
          'access' => ['type' => 'none'],
          'cache' => ['type' => 'none'],
          'title' => 'Search the programme',
          'empty' => [
            'area_text_custom' => [
              'id' => 'area_text_custom',
              'table' => 'views',
              'field' => 'area_text_custom',
              'plugin_id' => 'text_custom',
              'empty' => TRUE,
              'content' => 'No sessions matched — try describing what you want to learn.',
            ],
          ],
          'row' => [
            'type' => 'search_api',
            'options' => [
              'view_modes' => ['entity:node' => ['session' => 'teaser', 'speaker' => 'teaser']],
            ],
          ],
          'filters' => [
            'search_api_fulltext' => [
              'id' => 'search_api_fulltext',
              'table' => 'search_api_index_driftwood_content',
              'field' => 'search_api_fulltext',
              'plugin_id' => 'search_api_fulltext',
              'operator' => 'or',
              'exposed' => TRUE,
              'expose' => [
                'operator_id' => 'search_api_fulltext_op',
                'label' => 'What do you want to learn?',
                'identifier' => 'q',
              ],
            ],
          ],
          'pager' => ['type' => 'some', 'options' => ['items_per_page' => 10]],
          'use_ajax' => FALSE,
        ],
      ],
      'page_1' => [
        'display_plugin' => 'page',
        'id' => 'page_1',
        'display_title' => 'Page',
        'position' => 1,
        'display_options' => ['path' => 'search'],
      ],
    ],
  ])->save();
  echo "View camp_search at /search\n";
}
echo "Done.\n";
