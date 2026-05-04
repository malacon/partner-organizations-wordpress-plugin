# AI Usage: Sandcastle Issue 10

- Model/tool used: Sandcastle with Pi using GPT-5.5.
- How AI helped: inspected issue #10, reviewed project context and capability implementation, added documentation coverage tests, and drafted README/deployment guidance for Partner Manager permissions.
- Reviewed/corrected/rejected: verified capability names against `Capabilities`, `PostType`, and `Taxonomy`; kept the work documentation-only per issue scope; avoided suggesting administrator access as the default solution.
- Verification: ran `node tests/readme-docs.mjs` after the documentation changes; before commit, strongest available verification was run with `npm test`. Dockerized PHPUnit was attempted but Docker was unavailable in this harness.
- AI limitations/uncertainty: could not run Dockerized WordPress PHPUnit because Docker is not available in the environment.
- Security/maintainability checks: documented least-privilege `partner_manager` usage, explicit grant/revoke examples, non-destructive deactivation/reactivation behavior, and post-deploy role/capability verification.
