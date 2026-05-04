import { readFileSync } from 'node:fs';
import { join } from 'node:path';

const root = process.cwd();
const srcDir = join(root, 'partner-organizations', 'src');

function expect(condition, message) {
  if (!condition) throw new Error(message);
}

function php(className) {
  return readFileSync(join(srcDir, `${className}.php`), 'utf8');
}

const postType = php('PostType');
expect(postType.includes("public const SLUG = 'partner';"), 'Partner Organization CPT slug must be partner.');
expect(postType.includes("'public' => false"), 'Partner Organization CPT must not be publicly queryable as standalone pages.');
expect(postType.includes("'show_ui' => true"), 'Partner Organization CPT must be visible in the admin UI.');
expect(postType.includes("'supports' => ['title', 'thumbnail']"), 'Partner Organization CPT should support only title and featured image for issue 2.');

const taxonomy = php('Taxonomy');
expect(taxonomy.includes("public const SLUG = 'partner_category';"), 'Partner Category taxonomy slug must be partner_category.');
expect(taxonomy.includes('[PostType::SLUG]'), 'Partner Category taxonomy must be attached to Partner Organizations.');
expect(taxonomy.includes("'hierarchical' => true"), 'Partner Category taxonomy should be hierarchical.');
expect(taxonomy.includes('enforce_single_partner_category'), 'Partner Organization saves should enforce zero-or-one Partner Category.');
expect(taxonomy.includes('wp_set_object_terms('), 'Partner Category enforcement should trim saved terms to one.');

const activation = php('Activation');
for (const term of ['Education', 'Nonprofit', 'Corporate']) {
  expect(activation.includes(`'${term}'`), `Activation must create default Partner Category ${term}.`);
}
expect(activation.includes('term_exists('), 'Activation must create default Partner Categories idempotently.');
expect(activation.includes('wp_insert_term('), 'Activation must insert missing default Partner Categories.');

const metaBoxes = php('MetaBoxes');
expect(metaBoxes.includes('register_post_meta('), 'Website URL must be registered post meta.');
expect(metaBoxes.includes("'type' => 'string'"), 'Website URL post meta must be registered as string.');
expect(metaBoxes.includes("'single' => true"), 'Website URL post meta must be single.');
expect(metaBoxes.includes('wp_verify_nonce('), 'Website URL save must verify a nonce.');
expect(metaBoxes.includes("current_user_can('edit_post', $post_id)"), 'Website URL save must check edit_post capability.');
expect(metaBoxes.includes('FILTER_VALIDATE_URL'), 'Website URL save must validate URL syntax.');
expect(metaBoxes.includes("in_array($scheme, ['http', 'https'], true)"), 'Website URL save must allow only http and https schemes.');
expect(metaBoxes.includes('add_settings_error('), 'Invalid Website URL must create an admin notice.');
expect(metaBoxes.includes('delete_post_meta('), 'Empty Website URL must delete stored meta.');
expect(metaBoxes.includes('update_post_meta('), 'Valid Website URL must be saved.');

const adminColumns = php('AdminColumns');
for (const column of ['partner_organization_logo', 'taxonomy-partner_category', 'partner_organization_website']) {
  expect(adminColumns.includes(column), `Admin list table must include ${column} column.`);
}
expect(adminColumns.includes('get_the_post_thumbnail('), 'Logo admin column must render the featured image thumbnail.');
expect(adminColumns.includes('esc_url('), 'Website admin column must escape the URL.');
expect(adminColumns.includes('esc_html('), 'Website admin column must escape link text.');

console.log('Partner Organizations admin management checks passed.');
