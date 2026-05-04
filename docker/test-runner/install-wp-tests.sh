#!/usr/bin/env bash
set -euo pipefail

DB_NAME=${1:?Database name is required}
DB_USER=${2:?Database user is required}
DB_PASS=${3:?Database password is required}
DB_HOST=${4:?Database host is required}
WP_VERSION=${5:-latest}

WP_TESTS_DIR=${WP_TESTS_DIR:-/tmp/wordpress-tests-lib}
WP_CORE_DIR=${WP_CORE_DIR:-/tmp/wordpress}

mkdir -p "$(dirname "$WP_TESTS_DIR")" "$(dirname "$WP_CORE_DIR")"

if [ ! -d "$WP_CORE_DIR/wp-includes" ]; then
  rm -rf "$WP_CORE_DIR"
  mkdir -p "$WP_CORE_DIR"
  if [ "$WP_VERSION" = "latest" ]; then
    core_url="https://wordpress.org/latest.tar.gz"
  else
    core_url="https://wordpress.org/wordpress-${WP_VERSION}.tar.gz"
  fi
  curl -fsSL "$core_url" | tar --strip-components=1 -xz -C "$WP_CORE_DIR"
fi

if [ ! -d "$WP_TESTS_DIR/includes" ]; then
  rm -rf "$WP_TESTS_DIR"
  mkdir -p "$WP_TESTS_DIR"
  if [ "$WP_VERSION" = "latest" ]; then
    svn_url="https://develop.svn.wordpress.org/trunk/tests/phpunit"
    config_url="https://develop.svn.wordpress.org/trunk/wp-tests-config-sample.php"
  else
    svn_url="https://develop.svn.wordpress.org/tags/${WP_VERSION}/tests/phpunit"
    config_url="https://develop.svn.wordpress.org/tags/${WP_VERSION}/wp-tests-config-sample.php"
  fi
  svn export --quiet "$svn_url/includes" "$WP_TESTS_DIR/includes"
  svn export --quiet "$svn_url/data" "$WP_TESTS_DIR/data"
  curl -fsSL "$config_url" -o "$WP_TESTS_DIR/wp-tests-config.php"
fi

sed -i "s:dirname( __FILE__ ) . '/src/':'$WP_CORE_DIR/':" "$WP_TESTS_DIR/wp-tests-config.php"
sed -i "s/youremptytestdbnamehere/$DB_NAME/" "$WP_TESTS_DIR/wp-tests-config.php"
sed -i "s/yourusernamehere/$DB_USER/" "$WP_TESTS_DIR/wp-tests-config.php"
sed -i "s/yourpasswordhere/$DB_PASS/" "$WP_TESTS_DIR/wp-tests-config.php"
sed -i "s|localhost|$DB_HOST|" "$WP_TESTS_DIR/wp-tests-config.php"
