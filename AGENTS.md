<!-- ai-best-practices:start -->
<!-- Do not edit by hand inside the ai-best-practices markers in AGENTS.md; this block is regenerated when you update drupal/ai_best_practices. -->

## Drupal AI best practices

This project uses [`drupal/ai_best_practices`](https://www.drupal.org/project/ai_best_practices)
to provide AI guidance tailored for Drupal development.

**Skill discovery:** Skills are installed into `.agents/skills/` when you run
`composer install` or `composer update`. AI clients that support the
[Agent Skills specification](https://agentskills.io/specification) load skills
automatically from that directory — no manual listing needed. For clients that
do not yet support automatic discovery, this file (`AGENTS.md`) acts as a
compatibility fallback; add explicit skill references here only if your tooling
requires it.

**What to commit:** Add `.agents/` and `AGENTS.md` to version control so all
team members and CI environments share the same AI context. Also commit
tool-specific files such as `CLAUDE.md` and `GEMINI.md` if your team uses those
clients.

<!-- ai-best-practices:end -->
