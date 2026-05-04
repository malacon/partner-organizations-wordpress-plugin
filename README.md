# Partner Organizations WordPress Plugin

## Local Development

This project uses a small Docker Compose setup: nginx Alpine, WordPress PHP-FPM Alpine, and MariaDB.

```bash
docker compose up -d
```

Open WordPress at:

```text
http://localhost:12315
```

The plugin directory is mounted from:

```text
./partner-organizations
```

## Automated Tests

Run the fully Dockerized test runner from a clean clone with:

```bash
docker compose --profile test run --rm plugin-tests
```

The test profile starts an isolated MariaDB test database, installs the WordPress PHPUnit test suite at runtime, runs PHP syntax linting, and runs PHPUnit. Clean up test containers and volumes with:

```bash
docker compose --profile test down -v --remove-orphans
```

To stop the development environment:

```bash
docker compose down
```

To remove database and WordPress volumes for a clean reset:

```bash
docker compose down -v
```
