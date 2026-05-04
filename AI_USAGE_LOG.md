# AI Usage Log

Raw project log of AI-assisted development. Summarize this in README.md before submission.

## 2026-05-04 — Full-session planning and setup log

Source: `full-session.html` session export, session id `019df094-4601-73b9-9556-b3eaa7981b36`, cwd `/Users/cbaker/code/@kyle/children`.

### Tools used

- Pi coding agent / ChatGPT-style assistant for planning, codebase setup, issue creation, and Sandcastle configuration.
- GitHub CLI (`gh`) for private repository and issue creation.
- Docker Compose for local WordPress development environment.
- Sandcastle with Pi configured for `openai-codex/gpt-5.5` for upcoming AFK implementation iterations.

### How AI helped

- Interpreted the take-home assignment and helped scope a focused WordPress Partner Directory plugin.
- Compared Laravel Valet with Docker and recommended Docker for clean-clone reproducibility.
- Created initial Docker Compose setup for WordPress, then revised it to a smaller nginx Alpine + WordPress PHP-FPM Alpine + MariaDB setup on port `12315`.
- Facilitated a domain-model interview and captured resolved language in `CONTEXT.md`.
- Produced an implementation plan and split it into GitHub issues using vertical slices.
- Created a private GitHub repository and issues `#1`–`#7` with `needs-triage` and `Sandcastle` labels.
- Installed/configured Sandcastle and copied GPT-5.5/Pi model configuration from the JPG platform project.
- Added Sandcastle prompts requiring per-issue AI usage notes and security/verification reporting.

### Decisions made with human review

- Store Partner Organizations as a WordPress custom post type, not a custom table.
- Use a custom taxonomy for Partner Category, intended as zero-or-one category per Partner Organization.
- Keep website URL and logo optional to reduce editorial friction.
- Use Featured Image as the Partner Organization logo.
- Use shortcode `[partner_directory]` instead of a Gutenberg block.
- Support shortcode category filtering by human-readable category slug.
- Use public read-only REST endpoint data because the directory is public.
- Use plugin-specific REST namespace `/wp-json/partner-organizations/v1/partners`, not the prompt's generic `custom/v1` example.
- Return predictable REST envelopes with `data` and `meta`.
- Implement REST pagination.
- Implement transient response caching.
- Implement app-level per-client REST rate limiting: 60 requests per 5 minutes, user ID when logged in and IP otherwise.
- Use standard WordPress admin screens, normal editorial capabilities, and no custom admin-only capability model.
- Split plugin code by responsibility under a modern PHP namespace.
- Put Composer inside `partner-organizations/` so the plugin is production-package-ready.
- Use Dockerized PHPUnit integration tests rather than relying on local PHP.
- Add minimal GitHub Actions CI.

### AI output changed, corrected, or rejected

- Initial AI recommendation used the larger `wordpress:apache` + `mysql:8` Docker setup; the user rejected it as too large, and the setup was changed to nginx Alpine + WordPress PHP-FPM Alpine + MariaDB.
- AI initially suggested manual verification over automated tests; the user required automated tests, so the plan changed to Dockerized WordPress PHPUnit tests.
- AI suggested using the assignment example namespace `custom/v1`; the user required a plugin/vendor-specific namespace, so the decision changed to `partner-organizations/v1` and was recorded in ADR `docs/adr/0001-public-partner-api-design.md`.
- AI suggested documenting production rate limiting only; the user required implementation, so app-level transient rate limiting was added to the plan.
- Sandcastle init produced a default Claude-oriented setup; it was revised to use Pi with `openai-codex/gpt-5.5` matching the JPG platform project.

### Verification performed so far

- Validated Docker Compose configuration with `docker compose config`.
- Removed prior Docker containers/volumes/images before switching to the smaller Compose stack.
- Created and pushed the private GitHub repository.
- Created GitHub issues and labels with `gh`.
- Built the Sandcastle Docker image `sandcastle:partner-organizations`.
- Verified npm test placeholder runs; full Dockerized plugin test runner is planned for issue `#6`.

### Security and maintainability checks planned

- Nonce verification and capability checks for Website URL meta box saves.
- URL sanitization and scheme validation limited to `http`/`https`.
- Output escaping in shortcode template.
- Public REST endpoint limited to published Partner Organizations and structured fields only.
- Pagination and max `per_page` to bound REST responses.
- Transient cache invalidation on Partner Organization or Partner Category changes.
- Per-client REST rate limiting before cache lookup.
- Avoid committing secrets: `.sandcastle/.env` and `.sandcastle/pi-agent/auth.json` are ignored.

## 2026-05-04 — Sandcastle configuration

Sandcastle configured for AFK issue implementation using Pi with `openai-codex/gpt-5.5`. The orchestration script logs each planner, implementer, and merger iteration here, while implementers are instructed to add per-issue notes under `docs/ai-usage/`.

## 2026-05-04T02:53:44.433Z

Sandcastle iteration 1 started. Planner model: openai-codex/gpt-5.5.

## 2026-05-04T02:54:06.218Z

Sandcastle iteration 1 started. Planner model: openai-codex/gpt-5.5.
## 2026-05-04T02:59:14.698Z

Sandcastle iteration 1 started. Planner model: openai-codex/gpt-5.5.

## 2026-05-04T03:04:36.948Z

Planner selected 1 issue(s) for iteration 1:
- #1: Bootstrap package-ready plugin skeleton (sandcastle/issue-1-bootstrap-package-ready-plugin-skeleton)

## 2026-05-04T03:14:00.779Z

Implementer finished for #1 (sandcastle/issue-1-bootstrap-package-ready-plugin-skeleton). Commits produced: 1. Model: openai-codex/gpt-5.5.

## 2026-05-04T03:14:00.779Z

Merger starting for iteration 1. Model: openai-codex/gpt-5.5. Branches:
- sandcastle/issue-1-bootstrap-package-ready-plugin-skeleton

## 2026-05-04T03:14:25Z — Merger entry

- Branches merged: `sandcastle/issue-1-bootstrap-package-ready-plugin-skeleton`.
- Model/tool used: Sandcastle with Pi using GPT-5.5.
- Verification run: attempted `docker compose --profile test run --rm plugin-tests` but Docker is unavailable in this environment (`docker: command not found`); ran `npm test` successfully. PHP CLI is also unavailable, so PHP linting could not be run.
- Conflicts or corrections made: none; merge was a fast-forward and retained `docs/ai-usage/sandcastle-issue-1.md`.

## 2026-05-04T03:20:09.951Z

Merger completed for iteration 1.

## 2026-05-04T03:20:09.951Z

Sandcastle iteration 2 started. Planner model: openai-codex/gpt-5.5.

## 2026-05-04T03:25:39.322Z

Planner selected 2 issue(s) for iteration 2:
- #2: Manage Partner Organizations in WordPress admin (sandcastle/issue-2-manage-partner-organizations-admin)
- #6: Add fully Dockerized automated test runner and CI (sandcastle/issue-6-dockerized-test-runner-ci)

## 2026-05-04T03:34:01.448Z

Implementer finished for #2 (sandcastle/issue-2-manage-partner-organizations-admin). Commits produced: 1. Model: openai-codex/gpt-5.5.

## 2026-05-04T03:34:01.451Z

Implementer finished for #6 (sandcastle/issue-6-dockerized-test-runner-ci). Commits produced: 1. Model: openai-codex/gpt-5.5.

## 2026-05-04T03:34:01.455Z

Merger starting for iteration 2. Model: openai-codex/gpt-5.5. Branches:
- sandcastle/issue-2-manage-partner-organizations-admin
- sandcastle/issue-6-dockerized-test-runner-ci


## 2026-05-04T03:45:00Z — Merger entry

- Branches merged: `sandcastle/issue-2-manage-partner-organizations-admin`, `sandcastle/issue-6-dockerized-test-runner-ci`.
- Model/tool used: Sandcastle with Pi using GPT-5.5.
- Verification run: attempted `docker compose --profile test run --rm plugin-tests` after each merge, but Docker is unavailable in this environment (`docker: command not found`); ran `npm test` successfully after each merge. PHP CLI is also unavailable, so PHP linting could not be run locally.
- Conflicts or corrections made: issue #2 fast-forwarded cleanly; issue #6 had a `package.json` test-script conflict resolved by preserving all checks (`bootstrap-skeleton`, `admin-management`, and `docker-test-runner`). Retained `docs/ai-usage/sandcastle-issue-2.md` and `docs/ai-usage/sandcastle-issue-6.md`.
## 2026-05-04T03:40:41.347Z

Merger completed for iteration 2.

## 2026-05-04T03:40:41.348Z

Sandcastle iteration 3 started. Planner model: openai-codex/gpt-5.5.

## 2026-05-04T03:46:05.986Z

Planner selected 2 issue(s) for iteration 3:
- #3: Render Partner Directory shortcode with category filtering (sandcastle/issue-3-render-partner-directory-shortcode-with-category-filtering)
- #4: Expose public Partner Organizations REST API (sandcastle/issue-4-expose-public-partner-organizations-rest-api)

## 2026-05-04T03:54:05.417Z

Implementer finished for #3 (sandcastle/issue-3-render-partner-directory-shortcode-with-category-filtering). Commits produced: 1. Model: openai-codex/gpt-5.5.

## 2026-05-04T03:54:05.417Z

Implementer finished for #4 (sandcastle/issue-4-expose-public-partner-organizations-rest-api). Commits produced: 1. Model: openai-codex/gpt-5.5.

## 2026-05-04T03:54:05.417Z

Merger starting for iteration 3. Model: openai-codex/gpt-5.5. Branches:
- sandcastle/issue-3-render-partner-directory-shortcode-with-category-filtering
- sandcastle/issue-4-expose-public-partner-organizations-rest-api

## 2026-05-04T04:05:00Z — Merger entry

- Branches merged: `sandcastle/issue-3-render-partner-directory-shortcode-with-category-filtering`, `sandcastle/issue-4-expose-public-partner-organizations-rest-api`.
- Model/tool used: Sandcastle with Pi using GPT-5.5.
- Verification run: attempted `docker compose --profile test run --rm plugin-tests` after each successful merge, but Docker is unavailable in this environment (`docker: command not found`); ran `npm test` successfully after each merge.
- Conflicts or corrections made: issue #3 fast-forwarded cleanly; issue #4 had a `package.json` test-script conflict resolved by preserving both shortcode-directory and REST API checks. Retained `docs/ai-usage/sandcastle-issue-3.md` and `docs/ai-usage/sandcastle-issue-4.md`.
## 2026-05-04T04:00:17.121Z

Merger completed for iteration 3.

## 2026-05-04T04:00:17.122Z

Sandcastle iteration 4 started. Planner model: openai-codex/gpt-5.5.

## 2026-05-04T04:05:38.333Z

Planner selected 1 issue(s) for iteration 4:
- #5: Add API caching and rate limiting (sandcastle/issue-5-add-api-caching-and-rate-limiting)

## 2026-05-04T04:13:37.105Z

Implementer finished for #5 (sandcastle/issue-5-add-api-caching-and-rate-limiting). Commits produced: 1. Model: openai-codex/gpt-5.5.

## 2026-05-04T04:13:37.106Z

Merger starting for iteration 4. Model: openai-codex/gpt-5.5. Branches:
- sandcastle/issue-5-add-api-caching-and-rate-limiting


## 2026-05-04T04:20:00Z — Merger entry

- Branches merged: `sandcastle/issue-5-add-api-caching-and-rate-limiting`.
- Model/tool used: Sandcastle with Pi using GPT-5.5.
- Verification run: attempted `docker compose --profile test run --rm plugin-tests`, but Docker is unavailable in this environment (`docker: command not found`); ran `npm test` successfully.
- Conflicts or corrections made: fast-forwarded cleanly; no conflicts or code corrections required. Retained `docs/ai-usage/sandcastle-issue-5.md`.
## 2026-05-04T04:19:23.838Z

Merger completed for iteration 4.

## 2026-05-04T04:19:23.839Z

Sandcastle iteration 5 started. Planner model: openai-codex/gpt-5.5.

## 2026-05-04T04:24:47.530Z

Planner selected 1 issue(s) for iteration 5:
- #7: Complete README, deployment notes, and AI Usage Notes (sandcastle/issue-7-complete-readme-deployment-notes-and-ai-usage-notes)

## 2026-05-04T04:32:17.764Z

Implementer finished for #7 (sandcastle/issue-7-complete-readme-deployment-notes-and-ai-usage-notes). Commits produced: 1. Model: openai-codex/gpt-5.5.

## 2026-05-04T04:32:17.764Z

Merger starting for iteration 5. Model: openai-codex/gpt-5.5. Branches:
- sandcastle/issue-7-complete-readme-deployment-notes-and-ai-usage-notes


## 2026-05-04T04:32:39Z — Merger entry

- Branches merged: `sandcastle/issue-7-complete-readme-deployment-notes-and-ai-usage-notes`.
- Model/tool used: Sandcastle with Pi using GPT-5.5.
- Verification run: attempted `docker compose --profile test run --rm plugin-tests`, but Docker is unavailable in this environment (`docker: command not found`); ran `npm test` successfully.
- Conflicts or corrections made: fast-forwarded cleanly; no conflicts or code corrections required. Retained `docs/ai-usage/sandcastle-issue-7.md`.
## 2026-05-04T04:38:11.674Z

Merger completed for iteration 5.

## 2026-05-04T04:38:11.674Z

Sandcastle iteration 6 started. Planner model: openai-codex/gpt-5.5.

## 2026-05-04T04:43:37.153Z

Planner selected 0 issue(s) for iteration 6:
- None

## 2026-05-04T04:43:37.153Z

Sandcastle orchestration finished.

## 2026-05-04T04:45:17.973Z

Sandcastle iteration 1 started. Planner model: openai-codex/gpt-5.5.

## 2026-05-04T04:50:37.107Z

Planner selected 1 issue(s) for iteration 1:
- #8: Add Gutenberg block for Partner Directory insertion (sandcastle/issue-8-add-gutenberg-block-for-partner-directory-insertion)

## 2026-05-04T04:59:33.070Z

Implementer finished for #8 (sandcastle/issue-8-add-gutenberg-block-for-partner-directory-insertion). Commits produced: 1. Model: openai-codex/gpt-5.5.

## 2026-05-04T04:59:33.071Z

Merger starting for iteration 1. Model: openai-codex/gpt-5.5. Branches:
- sandcastle/issue-8-add-gutenberg-block-for-partner-directory-insertion


## 2026-05-04T05:00:17Z — Merger entry

- Branches merged: `sandcastle/issue-8-add-gutenberg-block-for-partner-directory-insertion`.
- Model/tool used: Sandcastle with Pi using GPT-5.5.
- Verification run: attempted `docker compose --profile test run --rm plugin-tests`, but Docker is unavailable in this environment (`docker: command not found`); ran `npm test` successfully.
- Conflicts or corrections made: fast-forwarded cleanly; no merge conflicts. Preserved local REST sanitizer corrections and retained `docs/ai-usage/sandcastle-issue-8.md`.
## 2026-05-04T05:05:42.035Z

Merger completed for iteration 1.

## 2026-05-04T05:05:42.036Z

Sandcastle iteration 2 started. Planner model: openai-codex/gpt-5.5.

## 2026-05-04T05:11:01.631Z

Planner selected 0 issue(s) for iteration 2:
- None

## 2026-05-04T05:11:01.633Z

Sandcastle orchestration finished.

