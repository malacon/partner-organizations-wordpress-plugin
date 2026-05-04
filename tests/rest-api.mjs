import { readFileSync } from 'node:fs';
import { join } from 'node:path';

const root = process.cwd();
const srcDir = join(root, 'partner-organizations', 'src');

function expect(condition, message) {
  if (!condition) throw new Error(message);
}

const rest = readFileSync(join(srcDir, 'Rest.php'), 'utf8');

expect(rest.includes("private const NAMESPACE = 'partner-organizations/v1';"), 'REST namespace must be partner-organizations/v1.');
expect(rest.includes("'/partners'"), 'REST route must expose /partners.');
expect(rest.includes("'permission_callback' => '__return_true'"), 'Partners endpoint must be public.');
expect(rest.includes("'data' =>"), 'Partners endpoint must return a data envelope key.');
expect(rest.includes("'meta' =>"), 'Partners endpoint must return a meta envelope key.');
for (const field of ["'id'", "'name'", "'website_url'", "'logo'", "'category'"]) {
  expect(rest.includes(field), `Partner Organization item must include ${field}.`);
}
expect(rest.includes("'post_status' => 'publish'") || readFileSync(join(srcDir, 'QueryBehavior.php'), 'utf8').includes("'post_status' => 'publish'"), 'REST query must use published Partner Organizations only.');
expect(rest.includes("get_term_by('slug', $category, Taxonomy::SLUG)"), 'Category filtering must look up Partner Category by slug.');
expect(rest.includes("'paged' => $page"), 'Endpoint must pass page to WP_Query.');
expect(rest.includes("'posts_per_page' => $per_page"), 'Endpoint must pass per_page to WP_Query.');
expect(rest.includes('min(100,'), 'Endpoint must cap per_page at 100.');
expect(rest.includes("['status' => 400]"), 'Invalid pagination must return HTTP 400.');
expect(rest.includes('empty_envelope'), 'Unknown category slugs must return an empty successful envelope.');

console.log('Partner Organizations REST API checks passed.');
