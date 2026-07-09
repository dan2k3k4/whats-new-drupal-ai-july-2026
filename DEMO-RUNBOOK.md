# Demo runbook — "What's New in Drupal AI" (Zurich)

Slide numbers = current 31-slide deck. Word-for-word scripts → `SPEAKER-NOTES.md`.

## Pre-flight (before the talk)

```sh
vendor/bin/drush runserver 127.0.0.1:8899
vendor/bin/drush uli --uri=http://127.0.0.1:8899   # log in with this FIRST
```

- Pre-run the CCC **"before"** state (Demo 01 box below) — keep as tab or screenshot.
- Rehearse the Canvas "new component" prompt once in a real browser.
- Terminal in repo root, big font.

## All tabs — paste into openallurls.com (demo order)

```
http://127.0.0.1:8899/admin/ai/context/items
http://127.0.0.1:8899/admin/ai/context/settings/agents
http://127.0.0.1:8899/node/27
http://127.0.0.1:8899/admin/ai/context/usage
http://127.0.0.1:8899/canvas/editor/canvas_page/1
http://127.0.0.1:8899/welcome
http://127.0.0.1:8899/workshops
http://127.0.0.1:8899/evenings
http://127.0.0.1:8899/team
http://127.0.0.1:8899/search
http://127.0.0.1:8899/admin/content/pages
http://127.0.0.1:8899/admin/dashboard
http://127.0.0.1:8899/admin/config/ai/providers
```

Open these **after** logging in via `drush uli`, or admin pages will 403.

---

## 🎯 DEMO 01 · Context Control Center — slide 21 (~4 min)

| # | Do | Where |
|---|----|-------|
| 1 | Open **Driftwood brand voice**, then **Audience: Technical expert** | tab 1 · context items |
| 2 | Show `rewrite_expert` pinning the persona (*always include*) | tab 2 · agent settings |
| 3 | Show the pre-made **"before"** — generic output | prepped tab/screenshot |
| 4 | Run ⤵ and **talk over the ~40s** | terminal |
| 5 | Refresh, open both audience toggles → camp voice, "skip it unless…" | tab 3 · `/node/27` |
| 6 | Receipts: which context steered which call | tab 4 · usage |

```sh
drush php:script scripts/04-rewrite.php -- 27 --force
```

> **"Before" prep (pre-talk, not on stage):** unpublish the 3 context items →
> run the command above → screenshot `/node/27` → **publish the items again**.

- **Fallback:** LLM slow → all 40 sessions already have summaries; narrate an existing one.
- **Bonus beat:** edit any session, clear both summary fields, hit Save → agents refill during save.

---

## 🎨 DEMO 02 · Canvas AI — slide 22 (~5.5 min)

| # | Do | Where |
|---|----|-------|
| 1 | AI panel → **paste brief ⤵** → go → **keep talking, don't wait** | tab 5 · editor |
| 2 | While it builds (~2 min): tour the four AI-built pages | tabs 6–9 |
| 3 | Say: photos/icons are **enum props** — AI only picks approved assets | — |
| 4 | Say: copy is already in camp voice — **same CCC context as demo 1** | — |
| 5 | Reveal the generated page, scroll it | tab 5 · editor |
| 6 | Only if ahead of time: *"Create a testimonial quote component: quote text, camper name, year"* ⚠ rehearsed only | tab 5 · editor |

**The proven brief:**

> Design the full Driftwood TechCamp 2026 landing page. Structure: 1) hero with
> the dawn photo, the camp name, a subtitle about three days of talks, workshops
> and campfires by the lake, dates badge 16-18 September 2026, CTA to /search.
> 2) section heading introducing what happens at camp. 3) three cards: hands-on
> workshops (code icon), evenings by the campfire (flame icon), find your
> sessions with AI search (search icon, link to /search). 4) a feature section
> with the campfire photo about the evening community programme. 5) a feature
> section with the laptops photo, image on the right, about hands-on workshops
> with mentors. 6) closing CTA banner inviting people to browse the programme
> at /search. Write all copy in the camp voice.

### ↳ Coda (~60s) — covers slide 20's third promise ("what's new")

| # | Do | Where |
|---|----|-------|
| 1 | Search: `talks about deploying safely without downtime` → zero keyword overlap | tab 10 · `/search` |
| 2 | "Ask the programme" block: `Are there any talks about making deploys less scary?` | tab 10 · `/search` |
| 3 | If time: chatbot bubble → `Which beginner AI talks should I attend?` | any page |

---

## 📚 Slide 23 · AI Best Practices (~40s — no live coding at 30 min)

Show in editor: `CLAUDE.md` → `AGENTS.md` → `.agents/skills/` (14 Drupal skills).
With 40 min: live-code *"add a drush command that lists sessions missing audience summaries"*.

## 🔌 Slide 24 · Providers (one line)

Everything ran on **amazee.ai** — chat + embeddings + vector DB. (tab 13)

## ⭐ Slide 25 · Spotlights (one line)

Two spotlights live on this site: **AI Dashboard** (tab 12) and **AI Search Block**
("Ask the programme" on `/search`). Flowdrop not installed.

---

## 🚨 Panic sheet

| Symptom | Move |
|---|---|
| LLM slow/down | Summaries, pages, search index are pre-generated — narrate existing content |
| Canvas AI flub | `/welcome` `/workshops` `/evenings` `/team` are saved, published proof |
| Chatbot weird answer | `/search` results view shows the same index working deterministically |
| Site broken | `README-DEMO.md` rebuild steps; all scripts idempotent |
| Login lost | `vendor/bin/drush uli --uri=http://127.0.0.1:8899` |
