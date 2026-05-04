# Production Deployment Guide

This guide describes how to package and deploy the Partner Organizations WordPress plugin to a production site. It targets a WP Engine-style managed WordPress workflow, but the same principles apply to similar hosts with separate development, staging, and production environments.

For release execution, copy the companion [Production Deployment Checklist](production-deployment-checklist.md) into the deployment ticket.

## Scope and assumptions

- Deploy plugin code from the `partner-organizations/` plugin directory to `wp-content/plugins/partner-organizations`.
- Treat code deployment separately from database, content, taxonomy, user/role, and media migration.
- Do not include credentials, local database exports, or host-specific secrets in the plugin package.
- Confirm production backups and rollback access before touching production.

## Clean production package

Build the deployable artifact from a clean checkout after tests pass. The package should contain the plugin files WordPress needs at runtime:

- `partner-organizations/partner-organizations.php`
- `partner-organizations/src/`
- `partner-organizations/templates/`
- `partner-organizations/assets/`
- `partner-organizations/blocks/`
- `partner-organizations/composer.json`, if your host or release process keeps plugin metadata with the deployed plugin

Exclude development-only files and directories such as `.git/`, `.sandcastle/`, `node_modules/`, root-level test files, local Docker volumes, logs, temporary exports, and secrets. This plugin currently has no production Composer dependencies beyond the PHP platform requirement, so `partner-organizations/vendor/` is not expected unless a future release adds runtime dependencies.

Example package command from the repository root:

```bash
zip -r partner-organizations.zip partner-organizations \
  -x '*/.git/*' \
  -x '*/node_modules/*' \
  -x '*/vendor/*' \
  -x '*.log' \
  -x '.sandcastle/*'
```

Inspect the zip before upload and verify it expands to a single top-level `partner-organizations/` plugin directory.

## WP Engine-style staging workflow

1. Deploy the package to a development or staging environment first, using your host's Git deploy, SFTP, dashboard upload, or CI release mechanism.
2. Activate or update the plugin on staging.
3. Run the smoke tests below against staging and review PHP/application logs.
4. Confirm caching/CDN behavior, HTTPS asset URLs, and REST API responses through the same edge path production uses where possible.
5. Schedule the production release during an appropriate change window.
6. Deploy the same reviewed package to production.
7. Repeat activation, flushing, smoke tests, and log review on production.

WP Engine-style environments often provide convenient staging-to-production copy tools. Use those carefully. Code can usually be promoted independently, but database pushes can overwrite production Partner Organizations, Partner Categories, page content, users, settings, orders, comments, or other live content.

## Code vs. database/content migration

Do not push a staging database over production content just to deploy this plugin. Code deployment places PHP, block metadata, JavaScript, CSS, and templates under `wp-content/plugins/partner-organizations`; it does not require replacing the production database.

Plan database/content changes separately:

- **Partner Organizations, Partner Categories**: these are WordPress posts and taxonomy terms. Create or edit them directly in production, or migrate them with a reviewed import/export process.
- **Directory placement**: pages containing the `[partner_directory]` shortcode or Partner Directory Gutenberg block are content and should be managed as production content unless your release process explicitly migrates pages.
- **Role/capability state**: activation creates the `partner_manager` role, grants Partner Organization and Partner Category capabilities to that role, and grants those same plugin capabilities to administrators. User role assignments and custom capability changes may differ by environment; verify administrator and Partner Manager access to the Partner Organizations admin after deployment.
- **Settings/permalinks**: permalink state is stored in the database. Flush production rewrite rules after activation/update rather than copying staging options blindly.

## Media/uploads and partner logos

Partner Organization logos use WordPress featured images. The plugin package does not contain uploaded media files.

Before launch or content migration:

- Confirm every Partner Organization with a logo has the expected featured-image logo media in production.
- If migrating content from staging, migrate uploads with host tooling, SFTP, rsync, or a media-aware migration plugin, and preserve attachment IDs/URLs where possible.
- Verify image URLs are served over HTTPS and pass through the production CDN correctly.
- Avoid overwriting production `wp-content/uploads` unless the media migration has been reviewed and backed up.

## Activation and flushing steps

After deploying code to production:

1. Activate the plugin from **WP Admin → Plugins** or with WP-CLI:

   ```bash
   wp plugin activate partner-organizations
   ```

2. Confirm the **Partner Organizations** admin menu appears and default Partner Categories are present if this is a first activation.
3. Flush rewrite rules by saving **Settings → Permalinks** or running:

   ```bash
   wp rewrite flush
   ```

4. Clear plugin REST cache transients. The plugin invalidates its own caches when Partner Organizations or Partner Categories change, but a deployment can also delete plugin transients explicitly:

   ```bash
   wp transient delete --all
   ```

   On a shared production site, prefer a narrower host/database operation for `_transient_partner_organizations_%` keys if available.

5. Purge host/page/object caches and the CDN edge cache for affected pages and REST routes.
6. Verify HTTPS, trusted proxy/IP handling, CDN/WAF rate limiting, backups, and logs/monitoring are active.

## Post-deploy role and capability verification

The Partner Manager model is intended to avoid broad administrator grants. Do not grant full administrator access when a user only needs to maintain the Partner Directory.

Verify the default role and capabilities after activation or update:

```bash
wp role exists partner_manager
wp cap list partner_manager
wp cap list administrator | grep partner
wp user list --role=partner_manager
```

Assign or remove the role for individual users as needed:

```bash
wp user add-role 123 partner_manager
wp user remove-role 123 partner_manager
```

For a custom role, grant only the Partner Organization and Partner Category capabilities that role needs. For example, a constrained editor workflow might use:

```bash
wp cap add editor edit_partners publish_partners create_partners assign_partner_categories
wp cap remove editor edit_partners publish_partners create_partners assign_partner_categories
```

Equivalent reviewed PHP for a custom role:

```php
$role = get_role('editor');
$capabilities = ['edit_partners', 'publish_partners', 'create_partners', 'assign_partner_categories'];

if ($role) {
    foreach ($capabilities as $capability) {
        $role->add_cap($capability);
    }
}

// To revoke custom access later:
if ($role) {
    foreach ($capabilities as $capability) {
        $role->remove_cap($capability);
    }
}
```

The practical capability groups are:

- Basic admin/media access: `read`, `upload_files`.
- Partner Organization management: `create_partners`, `edit_partners`, `edit_others_partners`, `edit_published_partners`, `edit_private_partners`, `publish_partners`, `delete_partners`, `delete_others_partners`, `delete_published_partners`, `delete_private_partners`, `read_private_partners`, and WordPress mapped meta capabilities `edit_partner`, `read_partner`, and `delete_partner`.
- Partner Category management: `manage_partner_categories`, `edit_partner_categories`, `delete_partner_categories`, and `assign_partner_categories`.

Plugin deactivation is intentionally non-destructive and does not remove existing roles/caps. Reactivate the plugin to restore the default grants for `partner_manager` and administrators if a migration, role-management plugin, or manual change removed them.

## Post-deployment smoke tests

Run these checks on staging before production and again immediately after production deployment.

### Admin management

- Log in as an administrator.
- Open **Partner Organizations** and confirm existing Partner Organizations load.
- Create or update a draft test Partner Organization with a Website URL, exactly one Partner Category, and an optional featured-image logo media item.
- Confirm unauthorized users cannot manage Partner Organizations unless intentionally granted access.

### Shortcode rendering

- Open a page containing `[partner_directory]`.
- Confirm only published Partner Organizations render, cards are ordered by title, optional fields are omitted cleanly when empty, website links are HTTPS/http only, and logos load over HTTPS.
- Test a category-filtered shortcode such as `[partner_directory category="education"]` if used by production content.

### Block rendering

- Open a page using the Partner Directory Gutenberg block in the editor.
- Confirm the block is selectable, can be removed, and renders the same Partner Directory output on the frontend.
- If the block has a Partner Category slug configured, confirm it displays only matching published Partner Organizations.

### REST API behavior

- Request the public endpoint:

  ```bash
  curl 'https://example.com/wp-json/partner-organizations/v1/partners?page=1&per_page=10'
  ```

- Confirm the response is JSON with `data` and `meta`, includes only published Partner Organizations, includes expected category and logo fields, and returns successful empty results for unknown Partner Category slugs.
- If pretty REST URLs fail, flush rewrite rules and test the fallback route `/?rest_route=/partner-organizations/v1/partners`.
- Review that CDN/WAF and application rate limiting do not block normal traffic.

### Monitoring

- Check PHP error logs, WordPress debug logs if enabled, host access logs, and CDN/WAF dashboards.
- Watch REST error rates, cache hit behavior, 4xx/5xx responses, and frontend page rendering after the release.

## Rollback plan

Prepare rollback before deployment:

1. Keep the previous known-good `partner-organizations/` package or release identifier.
2. Ensure current database and uploads backups are available and restorable.
3. If the new release fails, redeploy the previous plugin package to `wp-content/plugins/partner-organizations` or use the host's release rollback.
4. Purge page/object/CDN caches and delete plugin transients after rollback.
5. Flush rewrite rules if REST routes, activation state, or permalink behavior changed.
6. Re-run the smoke tests above.
7. Restore database/uploads only if the incident involved content or media changes; do not roll back the database for a code-only problem unless required and approved.

## Production controls checklist

- HTTPS enforced for admin, frontend, REST, and uploaded logo assets.
- Recent backups for code, database, and uploads.
- Host/page/object cache purge process documented.
- CDN/WAF rules and rate limiting in place for public REST traffic.
- Logs/monitoring available to operators during and after deployment.
- WordPress core, PHP, themes, and other plugins patched and compatible.
- No development-only files, secrets, or local artifacts included in the plugin package.
