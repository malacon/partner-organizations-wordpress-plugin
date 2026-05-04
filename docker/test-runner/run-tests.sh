#!/usr/bin/env bash
set -euo pipefail

DB_NAME=${WORDPRESS_TEST_DB_NAME:-wordpress_test}
DB_USER=${WORDPRESS_TEST_DB_USER:-wordpress}
DB_PASS=${WORDPRESS_TEST_DB_PASSWORD:-wordpress}
DB_HOST=${WORDPRESS_TEST_DB_HOST:-test-db}
WP_VERSION=${WP_VERSION:-6.6}
PHPUNIT_PHAR=${PHPUNIT_PHAR:-/tmp/phpunit.phar}

host_without_port=${DB_HOST%%:*}
port=${DB_HOST##*:}
if [ "$host_without_port" = "$port" ]; then
  port=3306
fi

for attempt in $(seq 1 60); do
  if mysqladmin ping --host="$host_without_port" --port="$port" --user="$DB_USER" --password="$DB_PASS" --silent >/dev/null 2>&1; then
    break
  fi
  if [ "$attempt" -eq 60 ]; then
    echo "Timed out waiting for test database at $DB_HOST" >&2
    exit 1
  fi
  sleep 2
done

install-wp-tests.sh "$DB_NAME" "$DB_USER" "$DB_PASS" "$DB_HOST" "$WP_VERSION"

if [ ! -f "$PHPUNIT_PHAR" ]; then
  curl -fsSL https://phar.phpunit.de/phpunit-9.6.phar -o "$PHPUNIT_PHAR"
  chmod +x "$PHPUNIT_PHAR"
fi

find /workspace/partner-organizations -name '*.php' -print0 | xargs -0 -n1 php -l
php "$PHPUNIT_PHAR" --configuration /workspace/phpunit.xml.dist
