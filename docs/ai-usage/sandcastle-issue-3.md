# Sandcastle Issue 3 AI Usage

- Model/tool used: Sandcastle with Pi using GPT-5.5.
- How AI helped: reviewed issue #3, project context, ADR, README, existing plugin classes, and tests; drafted shortcode PHPUnit coverage and implemented Partner Directory rendering with category filtering, template partial, and shortcode-only CSS enqueueing.
- What was reviewed, corrected, or rejected: preserved existing Partner Organization CPT/taxonomy/meta decisions; replaced permalink-based shortcode output with website-URL card links only when a safe URL exists; kept missing-logo cards text-only.
- Verification: `npm test` passed. `docker compose --profile test run --rm plugin-tests` and PHP linting could not run because Docker and PHP are unavailable in this harness.
- AI limitation/mistake or uncertainty: WordPress PHPUnit tests were added but could not be executed locally without Docker/PHP.
- Security/maintainability checks: sanitized shortcode category input, queried published Partner Organizations only, escaped dynamic template output, used a template partial, and enqueued namespaced frontend CSS only during shortcode rendering.
