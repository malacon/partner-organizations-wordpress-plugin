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
        add_action('delete_object_term_relationships', [$this, 'flush']);
        add_action('created_' . Taxonomy::SLUG, [$this, 'flush']);
        add_action('edited_' . Taxonomy::SLUG, [$this, 'flush']);
        add_action('delete_' . Taxonomy::SLUG, [$this, 'flush']);
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
        set_transient($this->version_key(), (string) microtime(true), DAY_IN_SECONDS);
    }

    private function key(string $key): string
    {
        return self::GROUP . '_' . sanitize_key($this->version() . '_' . $key);
    }

    private function version(): string
    {
        $version = get_transient($this->version_key());
        if (false === $version) {
            $version = '1';
            set_transient($this->version_key(), $version, DAY_IN_SECONDS);
        }

        return (string) $version;
    }

    private function version_key(): string
    {
        return self::GROUP . '_cache_version';
    }
}
