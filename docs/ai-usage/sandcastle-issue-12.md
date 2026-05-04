# AI Usage: sandcastle/issue-12

- Model/tool used: Sandcastle with Pi using GPT-5.5.
- How AI helped on this issue: inspected issue #12 and project docs, added a copy/paste-friendly production deployment checklist, linked it from README and the deployment guide, and added documentation coverage in the Node static test.
- What was reviewed, corrected, or rejected: reviewed `CONTEXT.md`, ADR 0001, `README.md`, `docs/deployment.md`, and documentation tests; corrected the checklist wording to satisfy the static documentation assertions; kept the change documentation-only and did not add deployment automation.
- Verification: `npm test` passed. `docker compose --profile test run --rm plugin-tests` could not run because Docker is not installed in this harness (`docker: command not found`).
- AI limitation/mistake or uncertainty: Dockerized WordPress/PHPUnit verification was unavailable, so verification is limited to the repository's Node/static tests in this environment.
- Security/maintainability checks performed: confirmed the checklist warns against deploying secrets/development artifacts, requires backup and rollback readiness, separates code deployment from database/content migration, and includes cache, transient, permalink, REST, role/capability, and log checks.
