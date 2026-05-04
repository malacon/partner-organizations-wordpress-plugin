FROM wordpress:cli-php8.2

USER root

RUN apk add --no-cache bash composer curl findutils mariadb-client subversion

COPY docker/test-runner/install-wp-tests.sh /usr/local/bin/install-wp-tests.sh
COPY docker/test-runner/run-tests.sh /usr/local/bin/plugin-test-runner
RUN chmod +x /usr/local/bin/install-wp-tests.sh /usr/local/bin/plugin-test-runner

WORKDIR /workspace

ENTRYPOINT ["plugin-test-runner"]
