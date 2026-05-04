<?php
/**
 * Shared transient cache behavior.
 *
 * @package PartnerOrganizations
 */

namespace PartnerOrganizations;

final class Cache implements Hookable
{
    public const GROUP = 'partner_organizations';

    public function register(): void
    {
        add_action('save_post_' . PostType::SLUG, [$this, 'flush']);
        add_action('deleted_post', [$this, 'flush']);
        add_action('set_object_terms', [$this, 'flush']);
    }

    public function get(string $key): mixed
    {
        return get_transient($this->key($key));
    }

    public function set(string $key, mixed $value, int $expiration = 300): bool
    {
        return (bool) set_transient($this->key($key), $value, $expiration);
    }

    public function flush(): void
    {
        delete_transient($this->key('rest_partners'));
    }

    private function key(string $key): string
    {
        return self::GROUP . '_' . sanitize_key($key);
    }
}
