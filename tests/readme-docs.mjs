import { readFileSync } from 'node:fs';

function expectIncludes(haystack, needle, message) {
  if (!haystack.includes(needle)) throw new Error(message);
}

const readme = readFileSync('README.md', 'utf8');

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
]) {
  expectIncludes(readme, detail, `README must document: ${detail}`);
}

console.log('README documentation checks passed.');
