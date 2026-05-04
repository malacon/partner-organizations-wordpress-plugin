import { readFileSync, existsSync } from 'node:fs';
import { join } from 'node:path';

const root = process.cwd();
const shortcode = readFileSync(join(root, 'partner-organizations', 'src', 'Shortcode.php'), 'utf8');
const template = readFileSync(join(root, 'partner-organizations', 'templates', 'partner-directory.php'), 'utf8');

function expect(condition, message) {
  if (!condition) throw new Error(message);
}

expect(shortcode.includes("add_shortcode('partner_directory'"), 'Partner Directory shortcode must be registered.');
expect(shortcode.includes("'category' => ''"), 'Partner Directory shortcode must accept a category attribute.');
expect(shortcode.includes('sanitize_title('), 'Partner Directory shortcode category input must be sanitized.');
expect(shortcode.includes("'taxonomy' => Taxonomy::SLUG"), 'Partner Directory shortcode must filter by Partner Category taxonomy.');
expect(shortcode.includes("'field' => 'slug'"), 'Partner Directory shortcode must filter Partner Category by slug.');
expect(shortcode.includes("'post_status' => 'publish'") || shortcode.includes('public_query_args('), 'Partner Directory shortcode must use published-only public query behavior.');
expect(shortcode.includes('wp_enqueue_style('), 'Partner Directory CSS must be enqueued when the shortcode renders.');
expect(shortcode.includes('templates/partner-directory.php'), 'Partner Directory shortcode must render through a template partial.');
expect(existsSync(join(root, 'partner-organizations', 'assets', 'css', 'partner-directory.css')), 'Partner Directory stylesheet must exist.');

expect(template.includes('No partner organizations found.'), 'Partner Directory template must render the friendly empty-results message.');
expect(template.includes('get_post_meta('), 'Partner Directory template must read Website URL meta.');
expect(template.includes('has_post_thumbnail()'), 'Partner Directory template must render logos only when present.');
expect(template.includes('esc_url('), 'Partner Directory template must escape Website URLs.');
expect(template.includes('esc_html(get_the_title())'), 'Partner Directory template must escape Partner Organization names.');
expect(template.includes('wp_kses_post(get_the_post_thumbnail('), 'Partner Directory template must safely render logo HTML.');

console.log('Partner Directory shortcode checks passed.');
