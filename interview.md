# Interview Preparation Notes

## Why use a Custom Post Type + taxonomy instead of custom tables or meta-only?

I modeled Partner Organizations as a WordPress Custom Post Type because they are editorial content records: editors need to create, edit, publish, draft, search, and manage them in the WordPress admin. A CPT gives us those workflows for free and keeps the implementation idiomatic.

Benefits of the CPT approach:

- **Native admin UI**: WordPress already provides list tables, publish/draft status, edit screens, featured images, bulk actions, and permissions.
- **Editorial lifecycle**: Partner Organizations can be drafted and reviewed before being published. The frontend and REST API only expose published partners.
- **Capability model**: We can reuse WordPress capabilities like `edit_post` instead of inventing a parallel authorization system.
- **Extensibility**: Themes, plugins, WP-CLI, import/export tools, and REST integrations already understand posts, post meta, media, and taxonomies.
- **Lower maintenance**: No custom schema migrations, no custom CRUD layer, and fewer deployment risks.

I used a taxonomy for Partner Category because category is classification data. A taxonomy is better than plain post meta when we need filtering, admin column support, REST exposure, slugs, and future extensibility.

I avoided custom tables because the data is small, content-like, and does not require specialized relational queries or high-volume analytics. A custom table would add migration complexity, backup/restore considerations, admin UI work, and more test surface without much benefit for this assignment.

I avoided meta-only categories because that would make filtering and admin management less idiomatic. Taxonomies already solve those problems.

## Why use Featured Image for logo?

I used the Featured Image field as the Partner Organization logo because logos are media assets and WordPress already has a strong media workflow.

Benefits:

- Editors can upload/select logos from the Media Library.
- WordPress handles attachment storage, thumbnails, image sizes, alt text, and URLs.
- No need to build a custom upload control or storage layer.
- The frontend template can use `get_the_post_thumbnail()` and REST can use `wp_get_attachment_image_url()`.
- It keeps the edit screen familiar to WordPress users.

The logo is optional. If a partner has no logo, the directory renders a clean text-only card instead of blocking publication.

## Why provide both shortcode and Gutenberg block?

The shortcode satisfies broad compatibility. It works in classic editor content, widgets, template areas, legacy sites, and places where block support is not available.

The Gutenberg block improves editor experience. Editors can discover it through the block inserter, configure it in the sidebar, preview the directory, and avoid memorizing shortcode syntax.

The block is dynamic/server-rendered and reuses shortcode rendering. That was intentional:

- One rendering path keeps shortcode and block output consistent.
- No frontend JavaScript is required to display the directory.
- Data stays server-rendered, cacheable, and accessible.
- Future block settings can be added without duplicating frontend templates.

## REST endpoint design

Endpoint:

```text
GET /wp-json/partner-organizations/v1/partners
```

### Public read-only

The endpoint is public because it exposes the same published Partner Directory data that appears on frontend pages. It does not expose drafts, private posts, edit links, nonces, unpublished metadata, or write operations.

### Namespace

I used `partner-organizations/v1` instead of a generic namespace like `custom/v1` because it avoids collisions and clearly communicates ownership and versioning. If the response shape changes later, a future `v2` route can be introduced without breaking existing consumers.

### Envelope response

The API returns an envelope with `data` and `meta`:

```json
{
  "data": [],
  "meta": {
    "page": 1,
    "per_page": 20,
    "category": "education",
    "total": 0,
    "total_pages": 0
  }
}
```

This is more stable than returning a bare array because pagination metadata and future fields can be added without changing the top-level response contract.

### Pagination

Pagination is required for a public endpoint because unbounded queries can become performance and abuse problems. The endpoint supports:

- `page`: positive integer, default `1`
- `per_page`: positive integer, default `20`, capped at `100`
- `category`: optional Partner Category slug

Invalid pagination returns HTTP 400. Unknown category slugs return an empty successful envelope because the request is valid but no matching content exists.

## Security review

### Admin saves: nonces and capabilities

The Website URL meta box uses a nonce to protect against CSRF. On save, the plugin checks:

- not autosave
- not revision
- nonce exists and verifies
- current user can `edit_post`

That means only authorized editors can update Partner Organization metadata through the admin form.

### Sanitization

The Website URL is sanitized and restricted to `http` and `https`. Empty values are allowed. Invalid URLs are not saved, and an admin notice explains the issue.

REST parameters are validated/sanitized:

- `page` and `per_page` must be positive integers.
- `per_page` is capped at 100.
- `category` is sanitized as a slug through a callback that safely handles missing or non-scalar values.

### Escaping

Frontend/admin output is escaped based on context:

- URLs with `esc_url()` / `esc_url_raw()`
- attributes with `esc_attr()`
- text with `esc_html()`
- generated image markup with `wp_kses_post()`

The REST endpoint only returns published partner data and formats optional fields as `null` when absent.

### Rate limiting

The public REST endpoint includes application-level transient-backed rate limiting. By default, it allows 60 requests per 5 minutes per logged-in user ID or anonymous IP address.

This is not a replacement for CDN/WAF rate limiting, but it is a useful portable safety layer inside the plugin. The policy is filterable through `partner_organizations_rate_limit_policy`.

## Caching and invalidation choices

The REST endpoint caches response envelopes in WordPress transients. The cache key includes the category, page, and per-page values so filtered and paginated responses are cached separately.

Caching is useful because the Partner Directory is read-heavy and changes infrequently. Public API requests can otherwise trigger repeated `WP_Query` calls.

Invalidation is handled through plugin cache hooks when relevant content changes, including Partner Organization and Partner Category changes. This keeps the cache simple and conservative: when partner data changes, cached directory responses are flushed.

Tradeoff: transient caching is portable and easy to deploy, but it is not as strong as persistent object caching, CDN caching, or edge caching. In production I would still recommend normal site-level caching and observability.

## Docker setup and test architecture

The local stack uses Docker Compose with:

- nginx Alpine
- WordPress PHP-FPM Alpine
- MariaDB

The local WordPress site runs on:

```text
http://localhost:12315
```

The plugin directory is mounted into WordPress, so local code changes are reflected immediately.

The test profile is isolated from the development site. It starts a test database, installs the WordPress PHPUnit test suite at runtime, lints PHP files, installs PHPUnit Polyfills, and runs PHPUnit.

Verification commands:

```bash
npm test
```

and:

```bash
docker compose --profile test run --rm plugin-tests
```

The Dockerized runner now passes locally:

```text
OK (13 tests, 60 assertions)
```

CI runs the same Dockerized test command through GitHub Actions on push and pull request. This keeps the local and CI test paths aligned.

The lighter `npm test` suite performs static repository checks around plugin structure, shortcode/block/REST implementation details, Docker runner files, and README coverage. It is fast feedback, while Dockerized PHPUnit is the stronger WordPress-integrated verification.

## AI usage: what was generated, corrected, rejected, and verified

AI was used as an implementation assistant, not as an unchecked source of truth.

AI helped with:

- breaking the assignment into GitHub issues
- generating initial plugin structure
- implementing CPT, taxonomy, shortcode, block, REST endpoint, caching, and rate limiting
- writing tests and Docker/CI setup
- drafting README, deployment notes, and AI usage notes
- diagnosing failing REST and block editor behavior
- fixing the Dockerized PHPUnit runner

Human direction/review guided:

- using CPT/taxonomy instead of custom tables
- keeping Website URL and logo optional
- using a plugin-specific REST namespace
- requiring pagination, caching, and rate limiting
- keeping tests Dockerized so local PHP is not required
- maintaining honest AI usage notes

AI suggestions that were corrected or rejected:

- A heavier Docker stack was replaced with a smaller nginx Alpine + WordPress PHP-FPM Alpine + MariaDB setup.
- Documentation-only rate limiting was rejected; actual application-level rate limiting was implemented.
- A generic REST namespace was replaced with `partner-organizations/v1`.
- The REST category sanitizer initially used `sanitize_title` directly as a REST callback. That failed because WordPress passes additional callback arguments. It was replaced with a wrapper callback that handles REST callback arguments safely.
- The Gutenberg block initially rendered in a way that made it hard to remove in the visual editor. It was fixed by using `useBlockProps()`.
- Dockerized PHPUnit initially failed because the WordPress test suite expected PHPUnit Polyfills configuration. The runner now installs `yoast/phpunit-polyfills` and exports `WP_TESTS_PHPUNIT_POLYFILLS_PATH`.
- PHPUnit then exposed repeated block registration during tests. Block registration was made idempotent.

Verification included:

- `npm test`
- `docker compose --profile test run --rm plugin-tests`
- manual local WordPress smoke testing
- REST curl/browser checks
- README and requirement audit

## Known tradeoffs and future improvements

### Tradeoffs

- **Zero-or-one category enforcement**: WordPress taxonomies naturally allow multiple terms. The plugin enforces one Partner Category after save. A custom admin UI could prevent selecting multiple upfront, but that would add complexity.
- **Transient rate limiting**: Portable and simple, but not as robust as Redis/object-cache backed rate limiting or CDN/WAF controls.
- **Transient caching**: Good enough for this plugin and easy to deploy, but production sites with persistent object cache/CDN would get better performance.
- **Dynamic block reuses shortcode rendering**: This avoids duplicated rendering logic, but the editor preview is simpler than a fully custom React-powered preview.
- **No custom table**: Correct for content-like partner records, but if the directory grew into a high-volume integration or analytics-heavy system, a custom table might become appropriate.

### Future improvements

- Add a richer block settings UI with a category dropdown populated from the REST API instead of a free-text slug field.
- Add admin validation that prevents selecting multiple Partner Categories before save.
- Add import/export tooling for large partner migrations.
- Add configurable cache TTL and rate-limit settings in the admin.
- Add E2E browser tests for shortcode/block rendering in real WordPress pages.
- Add visual regression tests for the directory cards.
- Add structured logging or metrics around REST cache hits, misses, and rate-limit events.
- Add WP-CLI commands for partner import, cache flush, and diagnostics.
- Add a production packaging script that excludes development-only files automatically.
