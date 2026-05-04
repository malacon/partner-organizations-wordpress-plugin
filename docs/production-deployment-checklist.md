# Production Deployment Checklist

Copy this checklist into the release ticket for each Partner Organizations production release. Keep environment names, URLs, package identifiers, backup IDs, and owners with the ticket.

## 1. Preflight and rollback readiness

- [ ] Confirm the production change window, release owner, technical reviewer, rollback owner, and communications channel.
- [ ] Confirm recent restorable database, files, and uploads backups; record backup IDs or host snapshot links.
- [ ] Keep the previous known-good plugin package or release identifier available for rollback.
- [ ] Verify package contents and version: the artifact expands to one `partner-organizations/` directory, includes runtime plugin files, excludes development-only files, and matches the expected plugin header/`PARTNER_ORGANIZATIONS_VERSION`.
- [ ] Confirm no database push from staging to production is planned unless separately reviewed and approved.
- [ ] Confirm featured-image logo media required for launch exists in production or has a reviewed uploads migration plan.
- [ ] Confirm WP Admin, WP-CLI or host dashboard, cache purge, log, monitoring, and rollback access are available.

## 2. Staging validation

- [ ] Deploy to staging before production using the same reviewed package intended for production.
- [ ] Activate or update the plugin on staging.
- [ ] Flush rewrite rules by saving **Settings → Permalinks** or running `wp rewrite flush`.
- [ ] Clear or delete plugin transients with the approved cache-clear process; if necessary, run `wp transient delete --all` on non-shared staging.
- [ ] Purge staging host, object, page, and CDN caches.
- [ ] Verify staging logs are clean before approving production deployment.
- [ ] Run the admin, frontend, REST, cache, and log smoke checks below against staging.

## 3. Production deployment and activation

- [ ] Put the site or release process in the approved deployment state for the change window.
- [ ] Deploy the same package validated on staging to `wp-content/plugins/partner-organizations`.
- [ ] Activate or update the plugin from **WP Admin → Plugins** or run `wp plugin activate partner-organizations`.
- [ ] Confirm the **Partner Organizations** admin menu appears.
- [ ] Flush rewrite rules by saving **Settings → Permalinks** or running `wp rewrite flush`.
- [ ] Delete plugin transients with the approved production method; use a narrow `_transient_partner_organizations_%` cleanup when available, or an approved `wp transient delete --all` if acceptable for the site.
- [ ] Purge host, object, page, and CDN caches for Partner Directory pages and REST routes.

## 4. Role, capability, and admin checks

- [ ] Confirm the Partner Manager role exists: `wp role exists partner_manager`.
- [ ] Confirm Partner Manager capabilities: `wp cap list partner_manager`.
- [ ] Confirm administrators have Partner Organization and Partner Category capabilities: `wp cap list administrator | grep partner`.
- [ ] Confirm intended users have the Partner Manager role: `wp user list --role=partner_manager`.
- [ ] Create, edit, and publish a test Partner Organization with a Website URL, exactly one Partner Category, and optional featured-image logo.
- [ ] Confirm unauthorized users cannot manage Partner Organizations unless intentionally granted custom capabilities.
- [ ] Remove or revert any temporary test content that should not remain public.

## 5. Frontend smoke checks

- [ ] Open a production page containing `[partner_directory]` and confirm only published Partner Organizations render.
- [ ] Check a category-filtered shortcode such as `[partner_directory category="education"]` if used by production content.
- [ ] Open a page using the Partner Directory Gutenberg block and confirm frontend output matches the expected directory.
- [ ] In the editor, confirm the Partner Directory Gutenberg block can be selected, configured, and removed.
- [ ] Confirm logos load over HTTPS/CDN, website links work, and empty optional fields are omitted cleanly.

## 6. REST, cache, permalink, and log checks

- [ ] Smoke test the unfiltered REST endpoint: `curl 'https://example.com/wp-json/partner-organizations/v1/partners?page=1&per_page=10'`.
- [ ] Smoke test a category-filtered REST endpoint: `curl 'https://example.com/wp-json/partner-organizations/v1/partners?category=education&page=1&per_page=10'`.
- [ ] Confirm REST responses are JSON envelopes with `data` and `meta`, include only published Partner Organizations, and include expected category/logo fields.
- [ ] If pretty REST URLs fail, flush rewrite rules again and test `https://example.com/?rest_route=/partner-organizations/v1/partners`.
- [ ] Confirm CDN/WAF and application rate limiting allow normal traffic and do not cache private/admin responses.
- [ ] Check PHP, WordPress, host, and CDN/WAF error logs for new warnings, fatals, REST 4xx/5xx spikes, or cache anomalies.

## 7. Rollback execution if needed

- [ ] Stop the rollout and notify the release channel.
- [ ] Redeploy the previous known-good plugin package or use the host release rollback.
- [ ] Purge host, object, page, and CDN caches after rollback.
- [ ] Delete plugin transients again using the approved production method.
- [ ] Flush rewrite rules if activation state, REST routes, or permalink behavior changed.
- [ ] Re-run role, admin, frontend, REST, cache, and log smoke checks.
- [ ] Restore database or uploads only for approved content/media incidents; do not restore the database for a code-only rollback unless required.

## 8. Incident follow-up and closeout

- [ ] Document what changed, what failed, customer impact, rollback actions, and follow-up owners.
- [ ] Record package version, deployment time, backup IDs, cache purges, smoke-test results, and log findings in the release ticket.
- [ ] Update monitoring, alerts, runbooks, or this checklist if the deployment revealed a missing signal or unclear step.
- [ ] Create follow-up issues for non-blocking defects or documentation improvements discovered during deployment.
