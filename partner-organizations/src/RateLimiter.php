<?php
/**
 * Transient-backed public API rate limiting.
 *
 * @package PartnerOrganizations
 */

namespace PartnerOrganizations;

final class RateLimiter implements Hookable
{
    private const DEFAULT_LIMIT = 60;
    private const DEFAULT_WINDOW = 300;

    public function register(): void
    {
        // Registered as a service for shared use by REST handlers.
    }

    public function client_id(): string
    {
        $user_id = get_current_user_id();
        if ($user_id > 0) {
            return 'user:' . $user_id;
        }

        $ip_address = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : 'unknown';
        return 'ip:' . $ip_address;
    }

    public function is_allowed(string $client_id): bool
    {
        $policy = $this->policy();
        if ($policy['limit'] <= 0 || $policy['window'] <= 0) {
            return true;
        }

        $key = 'partner_organizations_rate_' . md5($client_id);
        $now = time();
        $bucket = get_transient($key);

        if (! is_array($bucket) || ! isset($bucket['count'], $bucket['reset_at']) || (int) $bucket['reset_at'] <= $now) {
            $bucket = [
                'count' => 0,
                'reset_at' => $now + $policy['window'],
            ];
        }

        if ((int) $bucket['count'] >= $policy['limit']) {
            return false;
        }

        $bucket['count'] = (int) $bucket['count'] + 1;
        set_transient($key, $bucket, max(1, (int) $bucket['reset_at'] - $now));

        return true;
    }

    /**
     * @return array{limit:int,window:int}
     */
    private function policy(): array
    {
        $policy = apply_filters('partner_organizations_rate_limit_policy', [
            'limit' => self::DEFAULT_LIMIT,
            'window' => self::DEFAULT_WINDOW,
        ]);

        return [
            'limit' => max(0, absint($policy['limit'] ?? self::DEFAULT_LIMIT)),
            'window' => max(0, absint($policy['window'] ?? self::DEFAULT_WINDOW)),
        ];
    }
}
