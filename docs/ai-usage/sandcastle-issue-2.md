# Sandcastle Issue 2 AI Usage

- Model/tool used: Sandcastle with Pi using GPT-5.5.
- How AI helped: inspected issue #2 and required project context, added automated admin-management checks, and implemented WordPress admin management for Partner Organizations.
- Reviewed/corrected/rejected: changed the existing CPT slug from `partner_org` to the requested `partner`, narrowed the CPT to admin-visible/non-public standalone behavior, added registered website URL meta validation instead of accepting arbitrary sanitized URLs, and added single-category enforcement.
- Verification: ran `npm test` successfully. Attempted `docker compose --profile test run --rm plugin-tests`, but Docker is not installed in this environment. Attempted `php -l` checks, but PHP is not installed in this environment.
- AI limitation/mistake/uncertainty: could not execute WordPress/PHP integration tests locally due to missing Docker and PHP; validation is covered by static Node checks in this harness.
- Security/maintainability checks: used WordPress registration APIs, nonce and capability checks for meta saves, URL scheme validation for only empty/http/https values, admin notice on invalid input, sanitized input, escaped admin output, and responsibility-focused classes under `PartnerOrganizations\\`.
