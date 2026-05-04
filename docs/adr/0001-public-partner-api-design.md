# Public Partner API Design

The Partner Organizations plugin exposes published directory data through a public read-only REST endpoint under `partner-organizations/v1` rather than the assignment's generic `custom/v1` example to avoid namespace collisions and make ownership clear. Because the same Partner Organization data is public on the frontend, the endpoint does not require authentication, but it does require pagination, transient response caching, and per-client transient rate limiting so the public API remains bounded and production-minded.

## Consequences

- Public consumers should use `/wp-json/partner-organizations/v1/partners`.
- Production deployments should still prefer edge/CDN/WAF rate limiting in addition to the plugin's application-level limiter, especially behind trusted proxies.
