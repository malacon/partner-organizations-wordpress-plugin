import { readFileSync, existsSync } from 'node:fs';

function expect(condition, message) {
  if (!condition) throw new Error(message);
}

const compose = readFileSync('docker-compose.yml', 'utf8');
const runner = readFileSync('docker/test-runner/run-tests.sh', 'utf8');
const workflow = readFileSync('.github/workflows/tests.yml', 'utf8');
const readme = readFileSync('README.md', 'utf8');

expect(compose.includes('test-db:'), 'docker-compose.yml must define an isolated test-db service.');
expect(compose.includes('test_db_data:'), 'docker-compose.yml must use an isolated test database volume.');
expect(compose.includes('plugin-tests:'), 'docker-compose.yml must define a plugin-tests runner service.');
expect(compose.includes('healthcheck:'), 'test-db must include a healthcheck.');
expect(compose.includes('condition: service_healthy'), 'plugin-tests must wait for the test database healthcheck.');
expect(compose.includes('profiles: ["test"]'), 'test services must be behind the test profile.');
expect(runner.includes('install-wp-tests.sh'), 'test runner must install the WordPress test suite at runtime.');
expect(runner.includes('php -l'), 'test runner must run PHP syntax linting.');
expect(runner.includes('phpunit'), 'test runner must run PHPUnit tests.');
expect(workflow.includes('docker compose --profile test run --rm plugin-tests'), 'CI must run the Dockerized test command.');
expect(workflow.includes('docker compose --profile test down -v --remove-orphans'), 'CI must clean up test containers and volumes.');
expect(readme.includes('docker compose --profile test run --rm plugin-tests'), 'README must document the Dockerized test command.');
expect(existsSync('phpunit.xml.dist'), 'phpunit.xml.dist must be present.');

console.log('Dockerized test runner checks passed.');
