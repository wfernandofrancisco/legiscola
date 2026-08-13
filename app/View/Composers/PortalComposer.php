<?php

namespace App\View\Composers;

use App\Contracts\Services\Portal\PortalThemeServiceInterface;
use App\Models\Tenant;
use App\Models\TenantAdminSetting;
use App\Support\TenantContext;
use Illuminate\View\View;

class PortalComposer
{
    public function __construct(
        private PortalThemeServiceInterface $theme,
    ) {}

    public function compose(View $view): void
    {
        $tenantId = TenantContext::getTenantId();

        $tenant = $tenantId !== null ? Tenant::query()->find($tenantId) : null;
        $adminSettings = $tenant !== null
            ? TenantAdminSetting::query()->where('tenant_id', $tenant->id)->first()
            : null;

        $palette = $this->theme->palette($adminSettings);
        [$mapEmbed, $mapOpen] = $this->mapsUrls($tenant, $adminSettings);

        $view->with([
            'portalTenant' => $tenant,
            'portalAdminSettings' => $adminSettings,
            'portalThemeCss' => $this->theme->cssVariableString($adminSettings),
            'portalPalette' => $palette,
            'portalMapEmbedUrl' => $mapEmbed,
            'portalMapOpenUrl' => $mapOpen,
            'portalPlatform' => config('portal.platform', []),
        ]);
    }

    /**
     * Mapa: prioridade (1) lat/lng do tenant, (2) endereço em Configurações da câmara, (3) endereço cadastrado no tenant.
     *
     * @return array{0: string|null, 1: string|null} [iframe embed URL, browser “open in Google Maps” URL]
     */
    private function mapsUrls(?Tenant $tenant, ?TenantAdminSetting $settings): array
    {
        if ($tenant !== null
            && $tenant->latitude !== null
            && $tenant->longitude !== null
            && is_numeric($tenant->latitude)
            && is_numeric($tenant->longitude)) {
            $lat = (float) $tenant->latitude;
            $lng = (float) $tenant->longitude;

            return [
                'https://maps.google.com/maps?q='.$lat.','.$lng.'&z=16&output=embed',
                'https://www.google.com/maps/search/?api=1&query='.$lat.','.$lng,
            ];
        }

        $line = $this->mapsAddressLine($settings, $tenant);
        if ($line === '') {
            return [null, null];
        }

        $encoded = rawurlencode($line);

        return [
            'https://www.google.com/maps?q='.$encoded.'&output=embed',
            'https://www.google.com/maps/search/?api=1&query='.$encoded,
        ];
    }

    private function mapsAddressLine(?TenantAdminSetting $settings, ?Tenant $tenant): string
    {
        $fromSettings = [];
        if ($settings !== null) {
            $fromSettings = array_filter([
                $settings->logradouro,
                $settings->numero ? 'nº '.$settings->numero : null,
                $settings->bairro,
                $settings->cidade,
                $settings->uf,
                $settings->cep,
            ]);
        }

        if ($fromSettings !== []) {
            return implode(', ', $fromSettings).', Brasil';
        }

        if ($tenant === null) {
            return '';
        }

        $fromTenant = array_filter([
            $tenant->logradouro,
            $tenant->numero ? 'nº '.$tenant->numero : null,
            $tenant->bairro,
            $tenant->cidade,
            $tenant->estado,
            $tenant->cep,
        ]);

        if ($fromTenant === []) {
            return '';
        }

        return implode(', ', $fromTenant).', Brasil';
    }
}
