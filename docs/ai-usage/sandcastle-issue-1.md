# Sandcastle Issue 1 AI Usage

- Model/tool used: Sandcastle with Pi using GPT-5.5.
- How AI helped: inspected the issue and repository context, added a package-ready WordPress plugin skeleton, and created lightweight skeleton verification.
- Reviewed/corrected/rejected: corrected Composer PSR-4 escaping, used a Node-based test after PHP/Docker were unavailable in the environment, and kept implementation to issue 1 only.
- Verification: ran `npm test` successfully. Attempted `docker compose --profile test run --rm plugin-tests`, but Docker is not installed in this environment. Attempted PHP-based checks initially, but `php` is not installed.
- AI limitation/mistake/uncertainty: could not verify actual WordPress activation locally because Docker/PHP prerequisites are unavailable in the harness.
- Security/maintainability checks: used WordPress hooks and APIs, separated responsibilities under the `PartnerOrganizations\\` namespace, added nonce/capability checks for meta saves, sanitized stored URLs, and escaped admin/shortcode output.
