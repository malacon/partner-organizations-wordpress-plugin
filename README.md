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

To stop the environment:

```bash
docker compose down
```

To remove database and WordPress volumes for a clean reset:

```bash
docker compose down -v
```
