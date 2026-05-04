# Partner Organizations WordPress Plugin

A WordPress plugin for managing a public Partner Directory. Administrators create **Partner Organizations**, assign zero-or-one **Partner Category**, add an optional website URL and featured-image logo, then publish the directory through a Gutenberg block, shortcode, or read-only REST API.

## Clean-clone local setup

Prerequisites: Docker with Docker Compose, Git, and Node.js/npm for the lightweight repository checks.

1. Clone the repository and install Node dependencies:

   ```bash
   git clone <repo-url>
   cd children
   npm install
   ```

2. Start the local WordPress stack:

   ```bash
   docker compose up -d
   ```

3. Open WordPress at:

   ```text
   http://localhost:12315
   ```

The Docker setup uses nginx Alpine, WordPress PHP-FPM Alpine, and MariaDB. The plugin is mounted from `./partner-organizations` into WordPress as `wp-content/plugins/partner-organizations`, so local plugin edits are reflected in the container.

Useful cleanup commands:

```bash
docker compose down          # stop the local environment
docker compose down -v       # stop and remove WordPress/database volumes for a reset
```

## Plugin activation and manual demo

1. Complete the normal WordPress install screen at `http://localhost:12315`.
2. In the WordPress admin, go to **Plugins** and activate **Partner Organizations**. Activation creates the `partner_manager` role, grants Partner Organization and Partner Category capabilities to that role, and grants the same capabilities to administrators.
3. Go to **Partner Organizations** and add several Partner Organizations as an administrator or Partner Manager.
4. Add a title, optional **Website URL**, optional featured image logo, and one Partner Category. The plugin creates default Partner Categories on activation: Education, Nonprofit, and Corporate.
5. Publish the Partner Organizations. Drafts are intentionally hidden from the public Partner Directory and REST API.
6. Create or edit a page and insert the **Partner Directory** block, or add one of the shortcode examples below.
7. View the page and verify cards render alphabetically, omit missing optional fields, and category filters only show matching published Partner Organizations.
8. Verify the REST API with a browser or curl, for example `http://localhost:12315/wp-json/partner-organizations/v1/partners?page=1&per_page=10`.

## Gutenberg block usage

In the block editor for a post or page, click **Add block**, search for **Partner Directory**, and insert the block. The block renders the same frontend Partner Directory cards as the shortcode and does not require frontend JavaScript.

To filter the block, open the block settings sidebar and enter an optional **Partner Category slug**, such as `education`. Leave the setting blank to display all published Partner Organizations.

Use the shortcode instead when editing legacy content, classic-editor fields, widgets, or templates that do not support Gutenberg blocks.

## Shortcode usage

Render all published Partner Organizations:

```text
[partner_directory]
```

Filter by Partner Category slug:

```text
[partner_directory category="education"]
```

Limit the number rendered by the shortcode, capped internally at 100:

```text
[partner_directory category="education" per_page="10"]
```

Shortcode output uses a template partial in `partner-organizations/templates/partner-directory.php` and CSS enqueued only when `[partner_directory]` renders.

## Public REST API

Endpoint:

```text
GET /wp-json/partner-organizations/v1/partners
```

Examples:

```bash
curl 'http://localhost:12315/wp-json/partner-organizations/v1/partners'
curl 'http://localhost:12315/wp-json/partner-organizations/v1/partners?category=education&page=1&per_page=10'
```

Query parameters:

- `category`: optional Partner Category slug.
- `page`: positive integer, defaults to `1`.
- `per_page`: positive integer, defaults to `20`, capped at `100`.

The endpoint is public read-only because it exposes the same published data as the frontend Partner Directory. Responses use a stable envelope:

```json
{
  "data": [
    {
      "id": 123,
      "name": "Example Partner Organization",
      "website_url": "https://example.org",
      "logo": { "id": 456, "url": "https://example.test/logo.png", "alt": "Example logo" },
      "category": { "id": 7, "name": "Education", "slug": "education" }
    }
  ],
  "meta": { "page": 1, "per_page": 10, "category": "education", "total": 1, "total_pages": 1 }
}
```

Unknown category slugs return an empty successful envelope. Invalid pagination returns HTTP 400. If pretty REST URLs return redirects or theme HTML, save WordPress permalinks once in **Settings → Permalinks** to flush rewrite rules, or use the query-form fallback `/?rest_route=/partner-organizations/v1/partners`. Application-level controls include transient response caching for REST envelopes and transient rate limiting before cache lookup. The default limit is 60 requests per 5 minutes per logged-in user ID or anonymous IP address, and the policy can be changed with the `partner_organizations_rate_limit_policy` WordPress filter.

## Automated tests and CI

Run the fully Dockerized WordPress PHPUnit/lint test runner:

```bash
docker compose --profile test run --rm plugin-tests
```

Clean up test containers and volumes:

```bash
docker compose --profile test down -v --remove-orphans
```

The test profile starts an isolated MariaDB test database, installs the WordPress PHPUnit test suite at runtime, runs PHP syntax linting, and runs PHPUnit. GitHub Actions runs the same Dockerized command on push and pull request via `.github/workflows/tests.yml`, then removes test containers and volumes.

When Docker is unavailable, run the lighter static checks:

```bash
npm test
```

## Architecture and technical approach

- Plugin code lives under `partner-organizations/`, with responsibility-focused classes in the `PartnerOrganizations\\` namespace.
- `Plugin` composes services and registers WordPress hooks once.
- `PostType` registers the private admin-visible `partner` Custom Post Type for Partner Organizations using partner-specific mapped capabilities.
- `Taxonomy` registers the `partner_category` Partner Category taxonomy with partner-category-specific capabilities and enforces the zero-or-one Partner Category rule after save.
- `Capabilities` defines the Partner Organization and Partner Category capabilities, creates the `partner_manager` role on activation, and grants those capabilities to administrators.
- `MetaBoxes` handles Website URL storage with nonce verification, capability checks, and http/https-only sanitization.
- `Shortcode` renders the Partner Directory using shared public query behavior.
- `Block` registers the dynamic Gutenberg Partner Directory block and reuses shortcode rendering so block and shortcode output stay consistent.
- `Rest` exposes the public read-only API with pagination, category filtering, stable envelopes, and validation.
- `Cache` centralizes transient response caching and invalidation hooks for Partner Organization and Partner Category changes.
- `RateLimiter` centralizes per-client transient rate limiting for the public REST API.
- `QueryBehavior` keeps public queries consistently limited to published Partner Organizations ordered by title.

Key tradeoffs:

- A Custom Post Type and taxonomy keep the plugin idiomatic for WordPress editors and avoid custom-table maintenance.
- Featured Images are used for logos to reuse WordPress media workflows.
- The dynamic Gutenberg block gives editors a discoverable insertion flow while reusing shortcode rendering to avoid divergent frontend behavior.
- The shortcode remains appropriate for legacy content, classic-editor fields, widgets, and templates without block support.
- Application-level transient caching/rate limiting is portable, but production sites should still prefer edge/CDN/WAF controls for stronger abuse protection.
- The REST namespace is `partner-organizations/v1` rather than a generic example namespace to avoid collisions and clarify ownership.

Future improvements:

- Add richer block editor previews or category pickers backed by REST data.
- Add import/export tooling for large Partner Directory migrations.
- Add richer admin validation for category cardinality before save.
- Add configurable cache TTL and admin-facing rate-limit settings.
- Add visual regression or end-to-end browser tests for rendered directory pages.

## Production deployment notes

- Package and deploy the `partner-organizations/` plugin directory to `wp-content/plugins/partner-organizations` in the target WordPress installation.
- Activate the plugin through WP Admin or WP-CLI, then confirm default Partner Categories exist and permalinks are healthy.
- Ensure the site supports featured images and that uploads are correctly served over HTTPS for logo assets.
- Place the public REST API behind normal production controls: HTTPS, page/REST caching where appropriate, observability, CDN/WAF rate limiting, and trusted proxy/IP handling.
- Keep WordPress core, PHP, themes, and plugins patched; test plugin updates in staging with the Dockerized test runner or equivalent CI.
- Review backups and rollback steps before deploying content model changes to production.
- Do not deploy development-only files such as `node_modules/`, `.sandcastle/`, local database volumes, or unneeded test artifacts.

## AI Usage Notes

Required AI-assisted development notes are kept in `docs/ai-usage/`, one file per issue, with a raw project/session summary in `AI_USAGE_LOG.md`.

Tools used: Sandcastle with Pi using GPT-5.5, GitHub CLI, Docker Compose where available, npm/Node static checks, and repository editing tools.

How AI was used: AI helped inspect issues and project context, propose implementation slices, write code and tests, update documentation, and summarize verification and tradeoffs. Human review directed terminology, architecture decisions, test requirements, and corrections.

What was changed/reviewed: The implementation added the Partner Organizations plugin skeleton, admin management, shortcode rendering, public REST API, caching, rate limiting, Dockerized testing/CI, and this final README. Review focused on domain language, WordPress API usage, security checks, REST behavior, and documentation accuracy.

Verification: Per-issue notes document commands run. The strongest verification is `docker compose --profile test run --rm plugin-tests`, which has been run locally and passes. `npm test` provides lighter static checks for quick feedback or environments where Docker is unavailable.

AI mistakes/limitations: Earlier AI suggestions were adjusted or rejected, including a heavier Docker stack, relying on manual verification instead of automated tests, a generic REST namespace, and documentation-only rate limiting. AI could not verify Dockerized tests or PHP linting in environments lacking Docker/PHP.

Security and maintainability review: The project uses nonce and capability checks for admin saves, sanitizes Website URLs to http/https, escapes output, limits public queries to published Partner Organizations, caps pagination, uses REST caching invalidation hooks, applies per-client rate limiting, avoids committing secrets, and keeps production packaging concerns inside `partner-organizations/`.
