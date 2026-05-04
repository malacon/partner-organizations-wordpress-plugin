# AI Usage Notes: Sandcastle Issue 11

- Model/tool used: Sandcastle with Pi using GPT-5.5.
- How AI helped: inspected issue #11, reviewed required context docs, added a documentation regression check, drafted the production deployment guide, and updated the README summary/link.
- Reviewed/corrected/rejected: kept the scope documentation-only, used the project term Partner Organization, avoided adding deployment pipeline code or secrets, and made the guide target WP Engine-style workflows without requiring WP Engine-specific credentials.
- Verification: ran the new README/deployment documentation check first to confirm it failed before the guide existed, then ran `npm test` successfully after updates. Attempted `docker compose --profile test run --rm plugin-tests` as the strongest check, but it could not run because `docker` is unavailable in this harness.
- AI limitations/uncertainty: did not validate host-specific WP Engine commands against a live WP Engine environment; guidance remains operator-facing and host-agnostic where appropriate.
- Security/maintainability checks: documented separation of code from database/content/media migration, warned against overwriting production databases/uploads, included backups, rollback, HTTPS, cache/transient/permalink flushing, CDN/WAF rate limiting, monitoring, and exclusion of development-only artifacts.
