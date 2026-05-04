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
expect(!rest.includes("'sanitize_callback' => 'sanitize_title'"), 'REST category must not use sanitize_title directly because WordPress passes the request as the second callback argument.');
expect(rest.includes("'sanitize_callback' => [$this, 'sanitize_category_param']"), 'REST category must use a callback that tolerates missing/non-scalar values.');
expect(rest.includes('function sanitize_category_param'), 'REST category sanitizer must be defined.');
expect(rest.includes('! is_scalar($value)'), 'REST category sanitizer must handle missing/non-scalar values safely.');
expect(rest.includes("'paged' => $page"), 'Endpoint must pass page to WP_Query.');
expect(rest.includes("'posts_per_page' => $per_page"), 'Endpoint must pass per_page to WP_Query.');
expect(rest.includes('min(100,'), 'Endpoint must cap per_page at 100.');
expect(rest.includes("['status' => 400]"), 'Invalid pagination must return HTTP 400.');
expect(rest.includes('empty_envelope'), 'Unknown category slugs must return an empty successful envelope.');
expect(rest.includes('is_allowed($this->rate_limiter->client_id())'), 'Rate limiting must run before REST cache lookup using the shared client identity.');
expect(rest.includes("'status' => 429"), 'Exceeded REST rate limits must return HTTP 429.');

const rateLimiter = readFileSync(join(srcDir, 'RateLimiter.php'), 'utf8');
expect(rateLimiter.includes('private const DEFAULT_LIMIT = 60;'), 'Default REST rate limit must allow 60 requests.');
expect(rateLimiter.includes('private const DEFAULT_WINDOW = 300;'), 'Default REST rate limit window must be 5 minutes.');
expect(rateLimiter.includes('get_current_user_id()'), 'Rate limiter must identify logged-in clients by WordPress user ID.');
expect(rateLimiter.includes("$_SERVER['REMOTE_ADDR']"), 'Rate limiter must identify anonymous clients by IP address.');
expect(rateLimiter.includes("apply_filters('partner_organizations_rate_limit_policy'"), 'Rate limit policy must be filterable.');

const cache = readFileSync(join(srcDir, 'Cache.php'), 'utf8');
for (const hook of [
  "'save_post_' . PostType::SLUG",
  "'set_object_terms'",
  "'created_' . Taxonomy::SLUG",
  "'edited_' . Taxonomy::SLUG",
  "'delete_' . Taxonomy::SLUG",
]) {
  expect(cache.includes(hook), `Cache must flush on ${hook}.`);
}

console.log('Partner Organizations REST API checks passed.');
