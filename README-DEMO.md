# Driftwood TechCamp 2026 — AI demo site

Demo site for showing **Context Control Centre (ai_context)**, **Canvas AI**,
**AI Search (RAG)**, **AI Agents**, and **ai_best_practices** on Drupal 11,
themed as a fictional lakeside tech camp (40 sessions, 25 speakers).

## What's on the site

| Thing | Where |
|---|---|
| Semantic search page | `/search` — try "talks about deploying safely without downtime" |
| RAG chatbot | Bottom of any page — "Ask me anything about the programme" |
| Session pages with audience toggles | e.g. `/node/27` — 🌱 non-tech / 🔬 expert `<details>` toggles |
| Context Control Centre | `/admin/ai/context` — brand voice (global) + 2 audience personas |
| CCC per-agent wiring | `/admin/ai/context/settings/agents` |
| Rewrite agents | `/admin/config/ai/agents` — `rewrite_nontech`, `rewrite_expert` |
| Canvas editor + AI panel | `/canvas/editor/canvas_page/1` ("Camp landing page", unpublished) |
| Canvas SDCs to build with | Hero, Card, Section heading (module `driftwood_demo`) |
| AI coding guidance | `CLAUDE.md` → `AGENTS.md` → `.agents/skills/` (ai_best_practices) |

## Live demo moments

1. **CCC**: show a persona item at `/admin/ai/context/items`, then run the
   rewrite agent live on one session:
   `drush php:script scripts/04-rewrite.php -- <nid> --force`
   Refresh the session page; both audience toggles update. Usage appears at
   `/admin/ai/context/usage`.
2. **RAG**: type a paraphrase (no keyword overlap) into `/search`, then ask the
   chatbot a planning question ("which beginner AI talks should I attend?").
3. **Canvas AI**: open the Canvas editor, ask the AI panel to build a landing
   page for the camp using the Driftwood components. The global CCC brand-voice
   item is injected into the Canvas agents too — mention it (the generated copy
   says "campers" and stays plain-spoken because of it). Five designed
   components are available (Hero, Section heading, Card with icons, Feature
   split, CTA banner) with real photos (module `images/`) and Tabler icons
   exposed as enum props, so the AI can only pick valid assets. This full
   design brief is proven against the API — 8 components, correct photos and
   icons:
   > Design the full Driftwood TechCamp 2026 landing page. Structure: 1) hero
   > with the dawn photo, the camp name, a subtitle about three days of talks,
   > workshops and campfires by the lake, dates badge 16-18 September 2026,
   > CTA to /search. 2) section heading introducing what happens at camp.
   > 3) three cards: hands-on workshops (code icon), evenings by the campfire
   > (flame icon), find your sessions with AI search (search icon, link to
   > /search). 4) a feature section with the campfire photo about the evening
   > community programme. 5) a feature section with the laptops photo, image on
   > the right, about hands-on workshops with mentors. 6) closing CTA banner
   > inviting people to browse the programme at /search. Write all copy in the
   > camp voice.
4. **ai_best_practices**: open Claude Code in this repo, show the skills it
   picked up (`.agents/skills/`), live-code something small and Drupal-y
   (e.g. "add a drush command that lists sessions missing summaries").

Dry-run 1–3 on the venue network before the talk. All AI calls hit
`api.amazee.ai` — no offline fallback, but the pre-generated summaries and
indexed search keep working if you skip the live calls.

## Rebuild from scratch

```sh
composer install
# site install + copy your settings.local.php as usual
drush si minimal -y   # or reuse an existing DB-less install
# 1. Re-create the three amazee.ai keys (values NOT in git, see below):
#    /admin/config/system/keys — amazeeio_ai, amazeeio_ai_database, amazeeio_ai_management_token
drush cim -y                                     # all config (types, agents, index, view, block)
drush php:script scripts/01-content-model.php    # no-op after cim, safe either way
drush php:script scripts/02-import-content.php   # speakers, sessions, avatars
drush php:script scripts/03-ccc-setup.php        # CCC items + agent wiring (content entities)
drush php:script scripts/07-restore-summaries.php # pre-generated audience summaries, no LLM calls
drush search-api:index driftwood_content         # embed into amazee.ai vector DB (needs keys)
drush cr
```

To regenerate summaries with the LLM instead: `scripts/04-rewrite.php -- --all --force`.
Scripts `05`/`06` created the search server/index/assistant/view and are
superseded by config import; keep them as reference.

## Secrets

`config/sync/key.key.amazeeio_*.yml` are **gitignored on purpose** — the key
module stores the real API key values in config. Get the values from the
amazee.ai dashboard (or a teammate) and re-create the keys after `drush cim`.

`config/sync/ai_provider_amazeeio.settings.yml` is also gitignored (it carries
instance identifiers). On rebuild, configure the provider once at
`/admin/config/ai/providers/amazeeio` and it regenerates.

## Asset credits

Photos in `driftwood_demo/images/` are from Unsplash (Unsplash License, free
to use): dawn/campfire/tent/laptops/conference scenes. Icons in the Card
component are [Tabler Icons](https://tabler.io/icons) (MIT), inlined in the
twig. Display face: [Fraunces](https://fonts.google.com/specimen/Fraunces)
(OFL) via Google Fonts in `driftwood_demo/css/base.css` — swap for a local
copy if the venue network is untrusted.

## Serving locally

No web server is committed to this setup; for quick checks:
`drush runserver 127.0.0.1:8899` and `drush uli --uri=http://127.0.0.1:8899`.
