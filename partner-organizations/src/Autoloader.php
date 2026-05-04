<?php
/**
 * Fallback PSR-4 autoloader used when Composer dependencies have not been installed yet.
 *
 * @package PartnerOrganizations
 */

namespace PartnerOrganizations;

final class Autoloader
{
    public static function register(): void
    {
        spl_autoload_register(static function (string $class): void {
            $prefix = __NAMESPACE__ . '\\';

            if (0 !== strpos($class, $prefix)) {
                return;
            }

            $relative_class = substr($class, strlen($prefix));
            $file = __DIR__ . '/' . str_replace('\\', '/', $relative_class) . '.php';

            if (is_readable($file)) {
                require_once $file;
            }
        });
    }
}
