# Sandcastle issue 9 AI usage

- Model/tool used: Sandcastle with Pi using GPT-5.5.
- How AI helped: inspected issue #9, reviewed project context and existing WordPress plugin structure, added tests, implemented partner-specific capabilities, role grants, taxonomy capabilities, and documentation updates.
- What was reviewed/corrected/rejected: reviewed CPT, taxonomy, activation, meta authorization, README, and existing test patterns; kept deactivation non-destructive; avoided adding a custom role-management UI or changing public REST/frontend behavior.
- Verification: `npm test` passed. `docker compose --profile test run --rm plugin-tests` could not run because Docker is unavailable in this harness. `php -l ...` could not run because PHP CLI is unavailable in this harness.
- AI limitations/uncertainty: Dockerized PHPUnit could not be executed locally here, so the new PHPUnit coverage is written against WordPress test APIs but not run in this harness.
- Security/maintainability checks: used WordPress mapped meta capabilities, partner-category-specific taxonomy capabilities, activation-time role/cap grants, non-destructive deactivation, and `edit_post` meta authorization so Partner Managers do not need broad post permissions.
