<?php

/**
 * @file
 * Creates CCC context items (brand voice + 2 audience personas) and the two
 * rewrite agents wired to them. Run: drush php:script scripts/03-ccc-setup.php
 * Idempotent by label / id.
 */

use Drupal\ai_agents\Entity\AiAgent;

$storage = \Drupal::entityTypeManager()->getStorage('ai_context_item');

$items = [
  'Driftwood brand voice' => [
    'global' => TRUE,
    'purpose' => 'House style for all AI-generated or AI-edited content on the Driftwood TechCamp site.',
    'content' => "You are writing for Driftwood TechCamp 2026, a friendly three-day lakeside technology camp.\n\nVoice and tone:\n- Warm, welcoming and a little playful — like a good camp counsellor, never like corporate marketing.\n- Plain, concrete language. Short sentences. No buzzwords ('synergy', 'leverage', 'cutting-edge') and no exclamation-mark enthusiasm.\n- Honest above all: never oversell a session or hide its difficulty. Attendees plan their day around what we write.\n- Inclusive: assume readers of any background; explain or avoid insider jargon unless the audience is explicitly technical.\n- Refer to the event as 'the camp' and attendees as 'campers' where natural.",
  ],
  'Audience: Non-technical attendee' => [
    'global' => FALSE,
    'purpose' => 'Persona for rewriting session descriptions for readers without a technical background.',
    'content' => "The reader is a non-technical camp attendee: perhaps a project manager, marketer, designer, founder, or a curious partner of a developer. They do not write code and are not ashamed of it.\n\nWhen writing for them:\n- Translate every technical concept into everyday language or a concrete analogy. No acronyms without a plain-words gloss.\n- Answer their real questions: What is this actually about? Why does it matter? Will I be lost?\n- Be frank about fit using the session's stated experience level. A 'Beginner' developer talk can still assume coding knowledge — say so. Example framing: 'This is an introductory talk — you'll follow the big ideas even if the code examples wash over you' or 'This one assumes you already build software; you may enjoy the energy but expect to be lost by minute ten.'\n- Keep it to one friendly paragraph of roughly 80–120 words, ending with a clear 'should you go?' verdict.",
  ],
  'Audience: Technical expert' => [
    'global' => FALSE,
    'purpose' => 'Persona for rewriting session descriptions for highly experienced engineers.',
    'content' => "The reader is a senior engineer, architect or long-time open source contributor. They triage conference schedules ruthlessly and resent discovering a talk was beneath their level.\n\nWhen writing for them:\n- Be precise and dense; jargon is welcome when it is accurate. No motivational filler.\n- Calibrate against the session's stated experience level, and say it straight:\n  - Beginner session: 'This is an introductory session; skip it unless you want to support the speaker, accompany a junior colleague, or steal its teaching approach.'\n  - Intermediate session: name exactly which parts they likely know already and which corners might still be new.\n  - Expert session: state what makes it expert-level and how it might connect to their own production work.\n- Mention concrete technologies, patterns or failure modes covered so they can gauge novelty.\n- One tight paragraph, roughly 80–120 words, ending with a blunt attend/skip recommendation.",
  ],
];

$itemIds = [];
foreach ($items as $label => $def) {
  $existing = $storage->loadByProperties(['label' => $label]);
  if ($existing) {
    $itemIds[$label] = reset($existing)->id();
    continue;
  }
  $item = $storage->create([
    'type' => 'default',
    'label' => $label,
    'purpose' => $def['purpose'],
    'content' => $def['content'],
    'status' => 1,
    'moderation_state' => 'published',
  ]);
  if ($def['global']) {
    $item->setGlobal(TRUE);
  }
  $item->save();
  $itemIds[$label] = $item->id();
  echo "Context item: $label (id {$item->id()})\n";
}

$agents = [
  'rewrite_nontech' => ['Session rewriter: non-technical', 'Audience: Non-technical attendee', 'field_summary_nontech'],
  'rewrite_expert' => ['Session rewriter: technical expert', 'Audience: Technical expert', 'field_summary_expert'],
];

$prompt = <<<EOT
You rewrite conference session descriptions for a specific audience. The audience persona and the site's brand voice are provided as additional context — follow both exactly.

You will be given a session's title, track, experience level, length and original description. Write the audience-tailored version as instructed by the persona, including the honest should-you-attend guidance based on the session's experience level versus the reader's expertise.

Respond with ONLY the rewritten paragraph. No headings, no preamble, no quotation marks, no markdown.
EOT;

foreach ($agents as $id => [$label, $persona, $field]) {
  if (!AiAgent::load($id)) {
    AiAgent::create([
      'id' => $id,
      'label' => $label,
      'description' => "Rewrites session descriptions into $field for the '$persona' persona.",
      'system_prompt' => $prompt,
      'secured_system_prompt' => '[ai_agent:agent_instructions]',
      'tools' => [],
      'tool_settings' => [],
      'tool_usage_limits' => [],
      'default_information_tools' => '',
      'orchestration_agent' => FALSE,
      'triage_agent' => FALSE,
      'max_loops' => 3,
      'masquerade_roles' => [],
      'exclude_users_role' => FALSE,
      'structured_output_enabled' => FALSE,
      'structured_output_schema' => '',
      'guardrail_set' => '',
      'hostname_filter_disabled' => FALSE,
    ])->save();
    echo "Agent: $id\n";
  }
}

// Wire agents to their persona item in CCC per-agent config.
$config = \Drupal::configFactory()->getEditable('ai_context.agents');
$entries = $config->get('agents') ?? [];
foreach ($agents as $id => [$label, $persona, $field]) {
  $entry = [
    'id' => $id,
    'scope_subscriptions' => [],
    'always_include' => [(string) $itemIds[$persona]],
    'never_include' => [],
    'loop_aware' => FALSE,
  ];
  $found = FALSE;
  foreach ($entries as $i => $e) {
    if (($e['id'] ?? '') === $id) {
      $entries[$i] = $entry;
      $found = TRUE;
    }
  }
  if (!$found) {
    $entries[] = $entry;
  }
  echo "CCC wiring: $id -> always_include item {$itemIds[$persona]}\n";
}
$config->set('agents', $entries)->save();
echo "Done.\n";
