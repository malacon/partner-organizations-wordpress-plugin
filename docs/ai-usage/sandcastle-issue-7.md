# Sandcastle Issue 7 AI Usage

- Model/tool used: Sandcastle with Pi using GPT-5.5.
- How AI helped: Inspected issue #7, required context files, prior ADR/AI notes, Docker/CI configuration, and plugin classes; added a README documentation check and expanded README coverage for setup, activation/demo, shortcode, REST API, tests/CI, architecture, deployment, and AI usage.
- What was reviewed, corrected, or rejected: Verified documentation uses the domain term Partner Organization, documents the plugin-specific REST namespace, preserves Docker port 12315, and distinguishes app-level caching/rate limiting from recommended production CDN/WAF controls.
- How correctness was verified: Ran `npm test` after adding README coverage; attempted the Dockerized test runner but Docker is unavailable in this harness.
- AI limitation/mistake or uncertainty encountered: Could not execute `docker compose --profile test run --rm plugin-tests` locally because Docker is not installed in the harness; README examples were checked statically rather than against a live WordPress instance.
- Security/maintainability checks performed: Confirmed README documents nonce/capability, URL sanitization, escaping, published-only public output, pagination caps, cache invalidation/rate limiting, production HTTPS/CDN/WAF guidance, and deployment packaging boundaries.
