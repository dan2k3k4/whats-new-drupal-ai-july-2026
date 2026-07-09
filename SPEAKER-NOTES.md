# Speaker notes — word-for-word, per slide

Paste each block into the slide's speaker notes in Google Slides.
Style: fast, plain, general-audience. `[...]` = stage direction, don't read.
Talk time ≈ 17 min + clicking/latency ≈ 25–27 min total. Fits 30.

---

**Slide 1 — Title** (~20s)

Hi, I'm Dan, from amazee.io. Six months of Drupal AI — January to July, four releases. I'll tell you what got built and why it matters — and then I'll show you most of it live, on a real site. Let's go.

**Slide 2 — In one breath** (~15s)

Drupal AI in one breath: it's the AI foundation *inside* Drupal. Models, automation, content workflows. Any provider, open source, and your data stays under your control.

**Slide 3 — The six-month arc** (~15s)

The arc: one-point-three in March — biggest release ever. One-point-four in May — the enterprise release. Two-point-oh is in flight right now. New release every two months. This ecosystem *moves*.

**Slide 4 — The thesis** (~30s)

Here's the one idea behind everything today. Most organisations bolt AI on: a separate tool, data leaving the org, nobody can see what it's doing. Drupal's bet is the opposite — AI *inside* the platform. Governed by it. In the editor's real workflow. Monitored in production. Human in the loop, by design. Keep that in mind — every slide from here is that idea, applied.

**Slide 5 — By the numbers** (~15s)

And it's not experimental. Seventeen thousand sites in production. Forty-eight model providers. Three hundred contrib modules. This is infrastructure now.

**Slide 6 — 1.3.0 divider** (~5s)

March. One-point-three.

**Slide 7 — What organisations asked for** (~15s)

Organisations asked for three things: make it safe, make it useful for editors, make it visible in production. One-three delivered all three. In order —

**Slide 8 — Guardrails** (~25s)

Safety first — the headline feature: Guardrails. Configurable checks that run *before* a request leaves your site, and *after* the answer comes back. Block it, filter it, enforce policy — no code, and it applies to every AI operation on the site. Trust, built into the platform.

**Slide 9 — Why it matters for business** (~15s)

For the business: compliance gets one oversight point. Policies enforce themselves — not left to whoever's editing. And you get a real answer for clients who are nervous about AI.

**Slide 10 — Field Widget Actions** (~20s)

Making it useful for editors: one-click AI, right in the form they already use. Text to image. FAQ builder. CSV straight to a chart. Audio summaries. Over a hundred actions, every one customisable. No new tool to learn — that's the point.

**Slide 11 — Context-aware chatbot** (~15s)

The chatbot got context-aware — it knows what page you're on. "Make this title more engaging" — it knows *which* title. Small feature, huge difference in practice.

**Slide 12 — Better building blocks** (~10s)

For developers, quickly: reusable AI form elements, prompts stored as reviewable config, new operations like rerank and summarise. Less boilerplate.

**Slide 13 — Observability** (~25s)

And visibility — my favourite. OpenTelemetry, built in. Every AI interaction traced end to end, straight into Datadog, Grafana, Sentry — whatever you already run. Token spend tracked. Agent decisions auditable. Try asking a SaaS AI vendor for that.

**Slide 14 — 1.4.0 divider** (~5s)

Two months later: one-point-four.

**Slide 15 — Skills + drush generate** (~15s)

For developers: drush generate, for AI. Scaffold a provider, an automator, a guardrail — best-practice code in minutes, following the conventions.

**Slide 16 — Slack** (~10s)

Chat got decoupled from its processors. First payoff: talk to your Drupal agents straight from Slack. Meet people where they already are.

**Slide 17 — Automators × VBO** (~20s)

Automators meet bulk operations. Alt-text across an entire media library. Auto-tagging thousands of items. Batch translation. What used to be a migration backlog is now a one-click job.

**Slide 18 — Failover** (~10s)

Enterprise reliability: failover groundwork. Provider has an outage, traffic reroutes to a backup. Boring — which is exactly what you want.

**Slide 19 — Advanced guardrails** (~15s)

And guardrails grew up: global, site-wide enforcement. Applied to streaming responses as they flow. Input limits, so an oversized prompt doesn't cost you money. Production-hardened.

**Slide 20 — Live demos intro** (~15s)

Enough slides. Everything from here is live, on a real site — a fictional lakeside tech camp, forty sessions, twenty-five speakers, running today's modules on amazee.ai. If something breaks, that's the authentic part.

[SWITCH TO BROWSER — tabs pre-opened per DEMO-RUNBOOK.md]

**Slide 21 — LIVE DEMO 01 · Context Control Center** (~4 min)

This is the Context Control Centre. The problem it solves: every AI feature needs to know your brand voice, your audience, your rules — and you do *not* want to paste that into fifty prompts.

[Open /admin/ai/context/items — open "Driftwood brand voice"]
Here's our brand voice — written once, in plain markdown. Warm, honest, no buzzwords. And here — [open "Audience: Technical expert"] — an audience persona: a senior engineer who hates discovering a talk was beneath them.

[Open /admin/ai/context/settings/agents]
Each AI agent subscribes to context here. Our rewrite agent always gets the expert persona. One config screen — governance for every AI call.

Now watch it work. [Show the pre-made "before" — generic output — screenshot or tab] This is a session description rewritten *without* context. Competent. Generic. Could be any conference.

[Run: drush php:script scripts/04-rewrite.php -- 27 --force — narrate while it runs ~40s]
Same prompt, same model — but now the Context Control Centre injects the voice and the persona... [refresh /node/27, open both toggles] — camp voice, calibrated to the reader, and honest: "skip it unless you're mentoring someone." Nobody wrote that rule into this prompt. It came from the context centre.

[Optional receipt: /admin/ai/context/usage] And here's the audit trail — exactly which context steered which call.

**Slide 22 — LIVE DEMO 02 · Canvas AI** (~5.5 min)

Demo two: Canvas — Drupal's new page builder — with its AI assistant.

[Open /canvas/editor on a fresh page, open AI panel, paste the brief from README-DEMO.md, hit go — THEN keep talking over the ~2 min build]
I've just asked it, in plain language, for a full landing page: hero, sections, cards, photos, a call to action. While it thinks, the important part: it can only use *approved* components. Our photos and icons are locked-down choices — the AI picks from them, it can't invent assets. Governance again.

[While it builds, show /welcome, /workshops, /evenings, /team]
These four pages? All built this way, from different one-paragraph briefs. Different photos, different structure, same components — and notice the writing: "campers", plain-spoken, honest. That's the *same* brand-voice context from demo one, steering a completely different subsystem. Defined once.

[Return to editor — reveal the generated page, scroll it]
And there it is: hero, waterline, cards with the right icons, closing call to action — in the camp's voice.

[Coda — this replaces the deleted third demo, ~60s]
One more thing while we're here. [Open /search, run: "talks about deploying safely without downtime"] This search shares no keywords with the results — it's semantic, over a vector database. [Ask the block: "Are there any talks about making deploys less scary?"] And the same index powers this — a grounded answer, real sessions, real rooms and times, nothing invented. Same platform, same governance, three features.

**Slide 23 — AI Best Practices** (~40s)

[Back to slides]
Last one — don't miss this. If you use Claude Code, Cursor, any coding agent: install AI Best Practices. It's canonical Drupal guidance your agent reads — conventions, not guesses. Our demo site has it wired in; every AI-assisted line of code gets better. Also on this slide: Tool API — required in two-point-oh, write a tool once, use it from CLI, MCP and agents. Install the best practices today; adopt Tool API as you build.

**Slide 24 — Providers** (~15s)

Provider-agnostic in practice: forty-eight providers, swap without rewriting integration code. Everything you just watched ran on amazee.ai — chat, embeddings and the vector database, one provider.

**Slide 25 — Spotlight modules** (~20s)

Modules worth adopting. Two of these you just saw live: the AI Dashboard is the admin hub on our demo site, and AI Search Block is that grounded search on the search page. Agents move into core at two-point-oh. Screenshot this slide.

**Slide 26 — Real-world stories** (~40s)

This works outside demos. World Cancer Day: AI-moderated user stories, live, human-approved. Southwark Council: two thousand print PDFs turned into accessible web content — two hundred and forty times faster. DB Schenker: email triage collapsed from weeks to minutes. Rail support: eighty percent faster, satisfaction *up* twenty-five percent. Every one of these keeps a human in the loop.

**Slide 27 — Road to 2.0** (~15s)

Where it's heading: agents move into core. Tool API becomes the standard. The chatbot talks to agents directly. Two-point-oh is close.

**Slide 28 — Client value grid** (~15s)

If you're an agency, this slide is your client pitch: governance, productivity, scale, resilience, cost control — and no lock-in. Open source, forty-eight providers.

**Slide 29 — Where to start** (~15s)

Developers, start small: core plus one provider. Dashboard on. Try one field action. Turn on guardrails. That's an afternoon — and it's already useful.

**Slide 30 — Stay current** (~10s)

Staying current: project page, Drupal AI TV, the AI Learners Club, and the hash-ai channel on Drupal Slack. Release blogs are the source of truth.

**Slide 31 — Thank you** (~10s)

Six months of momentum. Thank you — questions here or in hash-ai on Slack. And go ship something.
