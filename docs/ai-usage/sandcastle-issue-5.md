# Sandcastle Issue 5 AI Usage

- Model/tool used: Sandcastle with Pi using GPT-5.5.
- AI helped inspect the existing REST API, transient cache, rate limiter, and test coverage, then implement bounded API caching and rate limiting behavior for issue #5.
- Reviewed project context, the public API ADR, README, issue #5, existing REST/cache/rate limiter classes, and PHPUnit/Node REST tests.
- Corrected the pre-existing one-minute IP-only limiter to a filterable 60-request/5-minute fixed-window policy that uses WordPress user ID for logged-in clients and IP address for anonymous clients.
- Added cache invalidation hooks for Partner Category creation, edits, and deletion in addition to Partner Organization and relationship changes.
- Verified with `npm test`. Attempted `docker compose --profile test run --rm plugin-tests`, but Docker is unavailable in this harness. Attempted PHP linting, but PHP CLI is unavailable in this harness.
- Security/maintainability checks: sanitized anonymous IP input, kept public route read-only, kept cache/rate-limit responsibilities in dedicated classes, and avoided adding secrets.
