# Sandcastle Issue 6 AI Usage

- Model/tool used: Sandcastle with Pi using GPT-5.5.
- How AI helped: Inspected the issue, repository context, existing Docker Compose setup, skeleton tests, and plugin bootstrap; added a Dockerized WordPress PHPUnit test runner, CI workflow, smoke PHPUnit test, and README documentation.
- What was reviewed, corrected, or rejected: Verified the runner keeps the WordPress test suite out of the repository and installs it at runtime; kept the development database separate from the test database; added static npm checks so the new infrastructure is covered when Docker is unavailable.
- How correctness was verified: Ran `npm test`, which passed skeleton checks and Docker test-runner configuration checks. Ran `bash -n` on the test runner scripts. Attempted `docker compose --profile test run --rm plugin-tests`, but Docker is not installed in this harness.
- AI limitation/mistake or uncertainty encountered: Could not execute the Dockerized PHPUnit runner locally because the harness lacks Docker.
- Security/maintainability checks performed: Used an isolated test database and volume, healthchecked database readiness, mounted source read-only in the test runner, avoided committing downloaded WordPress test-suite files, and ensured CI cleans up test containers and volumes.
