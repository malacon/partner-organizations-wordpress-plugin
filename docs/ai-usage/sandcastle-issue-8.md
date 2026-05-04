# Sandcastle Issue 8 AI Usage

- Model/tool used: Sandcastle with Pi using GPT-5.5.
- How AI helped: inspected the issue, project context, README, existing shortcode/query behavior, and tests; added a dynamic Gutenberg Partner Directory block, editor-only block assets, automated checks, and README documentation.
- What was reviewed, corrected, or rejected: reused the existing shortcode renderer instead of duplicating Partner Directory query/template logic; kept the block JavaScript editor-only and avoided unnecessary frontend JavaScript; verified terminology uses Partner Directory, Partner Organization, and Partner Category.
- Verification: `npm test` passed. `docker compose --profile test run --rm plugin-tests` could not run because Docker is unavailable in this harness. `php -l partner-organizations/src/Block.php && php -l partner-organizations/src/Plugin.php` could not run because PHP CLI is unavailable in this harness.
- AI limitation/mistake or uncertainty: Dockerized WordPress/PHPUnit and PHP syntax checks could not be executed locally, so block behavior that depends on WordPress runtime APIs is covered by committed PHPUnit tests but not run in this harness.
- Security/maintainability checks: sanitized the optional Partner Category slug with `sanitize_title()`, reused escaped shortcode template output for URLs/logos/titles, registered a dynamic block with a render callback, kept assets in the plugin package, and avoided frontend script registration.
