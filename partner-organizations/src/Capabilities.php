<?php
/**
 * Partner Organization role and capability definitions.
 *
 * @package PartnerOrganizations
 */

namespace PartnerOrganizations;

final class Capabilities
{
    public const ROLE = 'partner_manager';

    /**
     * @return string[]
     */
    public static function all(): array
    {
        return array_values(array_unique(array_merge(
            ['read', 'upload_files'],
            self::post_type_capabilities(),
            self::taxonomy_capabilities()
        )));
    }

    /**
     * @return string[]
     */
    public static function post_type_capabilities(): array
    {
        return [
            'edit_partner',
            'read_partner',
            'delete_partner',
            'edit_partners',
            'edit_others_partners',
            'delete_partners',
            'delete_others_partners',
            'publish_partners',
            'read_private_partners',
            'delete_private_partners',
            'delete_published_partners',
            'edit_private_partners',
            'edit_published_partners',
            'create_partners',
        ];
    }

    /**
     * @return string[]
     */
    public static function taxonomy_capabilities(): array
    {
        return [
            'manage_partner_categories',
            'edit_partner_categories',
            'delete_partner_categories',
            'assign_partner_categories',
        ];
    }

    public static function grant(): void
    {
        add_role(
            self::ROLE,
            __('Partner Manager', 'partner-organizations'),
            array_fill_keys(self::all(), true)
        );

        foreach ([self::ROLE, 'administrator'] as $role_name) {
            $role = get_role($role_name);
            if (null === $role) {
                continue;
            }

            foreach (self::all() as $capability) {
                $role->add_cap($capability);
            }
        }
    }
}
