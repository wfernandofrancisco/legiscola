<?php

namespace App\Contracts\Services\Portal;

use App\Models\TenantAdminSetting;

interface PortalThemeServiceInterface
{
    /**
     * @return array{primary:string,secondary:string,tertiary:string}
     */
    public function palette(?TenantAdminSetting $settings = null): array;

    /** Atributos style="" ou string para injetar em :root scoped. */
    public function cssVariableString(?TenantAdminSetting $settings = null): string;
}
