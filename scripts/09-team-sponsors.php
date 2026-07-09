<?php

/**
 * @file
 * Fills the team page (canvas_page 4) with org info + organisers, and adds
 * the sponsor strip to the homepage (canvas_page 1) before its CTA banner.
 * Run: drush php:script scripts/09-team-sponsors.php
 * Idempotent: skips a page if it already contains the component being added.
 */

use Drupal\canvas\Entity\Component;

$storage = \Drupal::entityTypeManager()->getStorage('canvas_page');
$uuid = \Drupal::service('uuid');

$item = function (string $component_id, array $inputs) use ($uuid) {
  $component = Component::load($component_id);
  return [
    'uuid' => $uuid->generate(),
    'component_id' => $component_id,
    'component_version' => $component->getActiveVersion(),
    'inputs' => $inputs,
    'label' => $component->label(),
  ];
};

// --- Team page (4): org info + organisers + closing CTA. ---
$team = [
  ['Dan Lemon', 'Camp Director', 'amazee.io', 'Started the camp after one too many hallway-track conversations got cut short by a fire alarm.'],
  ['Fatima El-Amrani', 'Community & Mentoring', 'Atlas CMS Collective', 'Has onboarded over a thousand first-time contributors and remembers most of their names.'],
  ['Kwame Mensah', 'Programme & Logistics', 'Accra Digital Lab', 'Seven years of meetup organising means the schedule holds even when the weather does not.'],
  ['Rosa Lindgren', 'Venue & Operations', 'Driftwood Lodge', 'Knows where every extension cord, spare chair and second coffee urn lives.'],
  ['Piotr Nowak', 'Sponsorships & Budget', 'Independent', 'Keeps ticket prices low and the spreadsheet honest.'],
  ['June Park', 'Volunteers & Accessibility', 'Lakeshore Library', 'Makes sure every camper can get to, into, and something out of every session.'],
];

$page = $storage->load(4);
$existing = array_column($page->get('components')->getValue(), 'component_id');
if (in_array('sdc.driftwood_demo.team_member', $existing)) {
  echo "Page 4 already has team members, skipping.\n";
}
else {
  $components = $page->get('components')->getValue();
  $components[] = $item('sdc.driftwood_demo.section_heading', [
    'kicker' => 'The organisation',
    'heading' => 'Run by volunteers, on purpose',
    'lead' => 'Driftwood TechCamp is organised by a small volunteer crew from the lakeside tech community. Nobody is paid; everybody is fed. Surplus from ticket sales goes back into next year\'s camp and travel grants for first-time speakers.',
  ]);
  $components[] = $item('sdc.driftwood_demo.feature', [
    'heading' => 'Why a camp and not a conference?',
    'text' => 'Because the best part of every conference was already the bit that felt like camp: the corridor chats, the late fire, the person who explained the thing properly at breakfast. We kept that part and built the programme around it.',
    'image' => '/modules/custom/driftwood_demo/images/campfire.jpg',
    'image_right' => TRUE,
  ]);
  $components[] = $item('sdc.driftwood_demo.section_heading', [
    'kicker' => 'The crew',
    'heading' => 'The organisers',
    'lead' => 'Six campers who answer the emails, book the boats and carry the projector.',
  ]);
  foreach ($team as [$name, $role, $org, $bio]) {
    $components[] = $item('sdc.driftwood_demo.team_member', [
      'name' => $name, 'role' => $role, 'org' => $org, 'bio' => $bio,
    ]);
  }
  $components[] = $item('sdc.driftwood_demo.cta_banner', [
    'heading' => 'Want to help carry the projector?',
    'text' => 'We take volunteers all year. Yes, volunteering comes with a ticket.',
    'cta_text' => 'Browse the programme',
    'cta_url' => '/search',
  ]);
  $page->set('components', $components);
  $page->set('path', ['alias' => '/team']);
  $page->set('status', TRUE);
  $page->save();
  echo "Page 4 filled: org info + " . count($team) . " organisers, alias /team\n";
}

// --- Homepage (1): sponsor strip before the CTA banner. ---
$page = $storage->load(1);
$components = $page->get('components')->getValue();
if (in_array('sdc.driftwood_demo.sponsors', array_column($components, 'component_id'))) {
  echo "Page 1 already has sponsors, skipping.\n";
}
else {
  $sponsors = $item('sdc.driftwood_demo.sponsors', [
    'heading' => 'Made possible by',
    'intro' => 'Ticket prices stay low because these fine folks chip in. Say hi — they send actual humans, not roll-up banners.',
    'sponsor1' => 'Pinewood Cloud',
    'sponsor2' => 'Loon Analytics',
    'sponsor3' => 'Ember & Oak Consulting',
    'sponsor4' => 'Lakeside Hosting Co.',
    'sponsor5' => 'Portage Software',
    'sponsor6' => 'Firefly Devtools',
  ]);
  // Insert before the closing CTA banner (last component) if present.
  $last = end($components);
  if ($last && $last['component_id'] === 'sdc.driftwood_demo.cta_banner') {
    array_splice($components, count($components) - 1, 0, [$sponsors]);
  }
  else {
    $components[] = $sponsors;
  }
  $page->set('components', $components);
  $page->save();
  echo "Page 1: sponsor strip added before the CTA banner\n";
}
