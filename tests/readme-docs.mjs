import { readFileSync } from 'node:fs';

function expectIncludes(haystack, needle, message) {
  if (!haystack.includes(needle)) throw new Error(message);
}

const readme = readFileSync('README.md', 'utf8');
const deploymentGuide = readFileSync('docs/deployment.md', 'utf8');
const deploymentChecklist = readFileSync('docs/production-deployment-checklist.md', 'utf8');

for (const heading of [
  '## Clean-clone local setup',
  '## Plugin activation and manual demo',
  '## Gutenberg block usage',
  '## Shortcode usage',
  '## Public REST API',
  '## Automated tests and CI',
  '## Architecture and technical approach',
  '## Production deployment notes',
  '## AI Usage Notes',
]) {
  expectIncludes(readme, heading, `README must include ${heading}.`);
}

for (const detail of [
  'http://localhost:12315',
  'docker compose up -d',
  'partner-organizations',
  'Partner Directory',
  'Partner Category slug',
  'frontend JavaScript',
  '[partner_directory]',
  '[partner_directory category="education"]',
  '/wp-json/partner-organizations/v1/partners',
  '?category=education&page=1&per_page=10',
  'public read-only',
  'transient response caching',
  '60 requests per 5 minutes',
  'docker compose --profile test run --rm plugin-tests',
  '.github/workflows/tests.yml',
  'Custom Post Type',
  'dynamic Gutenberg block',
  'Partner Category',
  'zero-or-one',
  'CDN/WAF rate limiting',
  'Sandcastle with Pi using GPT-5.5',
  'docs/ai-usage/',
  'has been run locally and passes',
  'partner_manager',
  'least-privilege role',
  'administrators receive the same Partner Organization and Partner Category capabilities',
  'WP-CLI',
  'wp user add-role 123 partner_manager',
  'wp cap add editor edit_partners publish_partners',
  'avoid granting full administrator access',
]) {
  expectIncludes(readme, detail, `README must document: ${detail}`);
}

for (const detail of [
  '# Production Deployment Guide',
  'WP Engine',
  'partner-organizations/` plugin directory',
  'zip -r partner-organizations.zip partner-organizations',
  'Do not push a staging database over production content',
  'Partner Organizations, Partner Categories',
  'featured-image logo media',
  'Activate the plugin',
  'flush rewrite rules',
  'delete plugin transients',
  'Rollback plan',
  '[partner_directory]',
  'Partner Directory Gutenberg block',
  '/wp-json/partner-organizations/v1/partners',
  'CDN/WAF',
  'backups',
  'Post-deploy role and capability verification',
  'wp role exists partner_manager',
  'wp cap list partner_manager',
  'wp user add-role 123 partner_manager',
  'wp cap remove editor edit_partners publish_partners',
  'Do not grant full administrator access',
  'Reactivate the plugin to restore the default grants',
]) {
  expectIncludes(deploymentGuide, detail, `Deployment guide must document: ${detail}`);
}

expectIncludes(
  readme,
  'See the detailed [Production Deployment Guide](docs/deployment.md)',
  'README must link to the detailed production deployment guide.',
);

expectIncludes(
  readme,
  '[Production Deployment Checklist](docs/production-deployment-checklist.md)',
  'README must link to the production deployment checklist.',
);

expectIncludes(
  deploymentGuide,
  '[Production Deployment Checklist](production-deployment-checklist.md)',
  'Deployment guide must link to the production deployment checklist.',
);

for (const detail of [
  '# Production Deployment Checklist',
  'Copy this checklist into the release ticket',
  'database, files, and uploads backups',
  'previous known-good plugin package',
  'Verify package contents and version',
  'Deploy to staging before production',
  'Activate or update the plugin',
  'wp role exists partner_manager',
  'wp cap list partner_manager',
  'Create, edit, and publish a test Partner Organization',
  '[partner_directory]',
  'Partner Directory Gutenberg block',
  '/wp-json/partner-organizations/v1/partners?page=1&per_page=10',
  '/wp-json/partner-organizations/v1/partners?category=education&page=1&per_page=10',
  'flush rewrite rules',
  'delete plugin transients',
  'Purge host, object, page, and CDN caches',
  'Check PHP, WordPress, host, and CDN/WAF error logs',
  'Redeploy the previous known-good plugin package',
  'Document what changed, what failed, customer impact, rollback actions, and follow-up owners',
]) {
  expectIncludes(deploymentChecklist, detail, `Deployment checklist must document: ${detail}`);
}

console.log('README documentation checks passed.');
