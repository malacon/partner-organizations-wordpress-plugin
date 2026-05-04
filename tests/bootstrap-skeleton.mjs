import { readFileSync, existsSync } from 'node:fs';
import { join } from 'node:path';

const root = process.cwd();
const pluginDir = join(root, 'partner-organizations');

function expect(condition, message) {
  if (!condition) throw new Error(message);
}

const composer = JSON.parse(readFileSync(join(pluginDir, 'composer.json'), 'utf8'));
expect(
  composer.autoload?.['psr-4']?.['PartnerOrganizations\\'] === 'src/',
  'Composer PSR-4 autoload must map PartnerOrganizations\\ to src/.'
);

const main = readFileSync(join(pluginDir, 'partner-organizations.php'), 'utf8');
for (const constant of [
  'PARTNER_ORGANIZATIONS_VERSION',
  'PARTNER_ORGANIZATIONS_FILE',
  'PARTNER_ORGANIZATIONS_DIR',
  'PARTNER_ORGANIZATIONS_URL',
  'PARTNER_ORGANIZATIONS_BASENAME',
]) {
  expect(main.includes(`define('${constant}'`), `${constant} is not defined in the main plugin file.`);
}
expect(main.includes('vendor/autoload.php'), 'Main plugin file does not load the Composer autoloader.');
expect(main.includes('Autoloader::register()'), 'Main plugin file does not register the fallback autoloader.');
expect(main.includes('register_activation_hook'), 'Activation hook is not wired.');
expect(main.includes('register_deactivation_hook'), 'Deactivation hook is not wired.');
expect(main.includes('Plugin::instance()->boot()'), 'Central plugin class is not booted.');

const requiredClasses = [
  'Plugin',
  'Activation',
  'Deactivation',
  'PostType',
  'Taxonomy',
  'MetaBoxes',
  'AdminColumns',
  'Shortcode',
  'Rest',
  'Cache',
  'RateLimiter',
  'QueryBehavior',
];

for (const className of requiredClasses) {
  const file = join(pluginDir, 'src', `${className}.php`);
  expect(existsSync(file), `${className}.php is missing.`);
  const contents = readFileSync(file, 'utf8');
  expect(contents.includes('namespace PartnerOrganizations;'), `${className}.php is not in the PartnerOrganizations namespace.`);
}

const plugin = readFileSync(join(pluginDir, 'src', 'Plugin.php'), 'utf8');
for (const service of ['PostType', 'Taxonomy', 'MetaBoxes', 'AdminColumns', 'Shortcode', 'Rest', 'Cache', 'RateLimiter', 'QueryBehavior']) {
  expect(plugin.includes(service), `Plugin.php does not reference ${service}.`);
}

console.log('Partner Organizations skeleton checks passed.');
