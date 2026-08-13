<?php

use App\Contracts\Services\Portal\PortalThemeServiceInterface;
use App\Models\TenantAdminSetting;

if (! function_exists('portal_palette')) {

    /** @return array{primary:string,secondary:string,tertiary:string} */
    function portal_palette(?TenantAdminSetting $settings = null): array
    {
        return app(PortalThemeServiceInterface::class)->palette($settings);
    }
}

if (! function_exists('portal_theme_css')) {

    function portal_theme_css(?TenantAdminSetting $settings = null): string
    {
        return app(PortalThemeServiceInterface::class)->cssVariableString($settings);
    }
}
