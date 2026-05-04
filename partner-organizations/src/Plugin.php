<?php
/**
 * Central plugin bootstrap.
 *
 * @package PartnerOrganizations
 */

namespace PartnerOrganizations;

final class Plugin
{
    private static ?self $instance = null;

    /** @var Hookable[] */
    private array $services;

    private bool $booted = false;

    private function __construct()
    {
        $cache = new Cache();
        $rate_limiter = new RateLimiter();
        $query_behavior = new QueryBehavior();
        $shortcode = new Shortcode($query_behavior);

        $this->services = [
            new PostType(),
            new Taxonomy(),
            new MetaBoxes($cache),
            new AdminColumns(),
            $shortcode,
            new Block($shortcode),
            new Rest($cache, $rate_limiter, $query_behavior),
            $cache,
            $rate_limiter,
            $query_behavior,
        ];
    }

    public static function instance(): self
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function boot(): void
    {
        if ($this->booted) {
            return;
        }

        foreach ($this->services as $service) {
            $service->register();
        }

        $this->booted = true;
    }
}
