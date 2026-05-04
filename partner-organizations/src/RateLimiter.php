<?php
/**
 * Transient-backed public API rate limiting.
 *
 * @package PartnerOrganizations
 */

namespace PartnerOrganizations;

final class RateLimiter implements Hookable
{
    private const LIMIT = 60;
    private const WINDOW = 60;

    public function register(): void
    {
        // Registered as a service for shared use by REST handlers.
    }

    public function is_allowed(string $client_id): bool
    {
        $key = 'partner_organizations_rate_' . md5($client_id);
        $count = (int) get_transient($key);

        if ($count >= self::LIMIT) {
            return false;
        }

        set_transient($key, $count + 1, self::WINDOW);
        return true;
    }
}
