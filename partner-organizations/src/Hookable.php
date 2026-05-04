<?php
/**
 * Shared interface for classes that register WordPress hooks.
 *
 * @package PartnerOrganizations
 */

namespace PartnerOrganizations;

interface Hookable
{
    public function register(): void;
}
