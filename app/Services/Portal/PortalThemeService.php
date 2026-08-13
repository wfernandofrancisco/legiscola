<?php

namespace App\Services\Portal;

use App\Contracts\Services\Portal\PortalThemeServiceInterface;
use App\Models\TenantAdminSetting;

class PortalThemeService implements PortalThemeServiceInterface
{
    /** Azul real institucional */
    private const DEFAULT_PRIMARY = '#1d4ed8';

    /** Azul-marinho profundo — sobriedade governamental */
    private const DEFAULT_SECONDARY = '#0f2942';

    /** Ciano moderno — contraste limpo sem conflitar com o azul */
    private const DEFAULT_TERTIARY = '#0891b2';

    public function palette(?TenantAdminSetting $settings = null): array
    {
        return [
            'primary' => $this->normalizeHex($settings?->primary_color, self::DEFAULT_PRIMARY),
            'secondary' => $this->normalizeHex($settings?->secondary_color, self::DEFAULT_SECONDARY),
            'tertiary' => $this->normalizeHex($settings?->tertiary_color, self::DEFAULT_TERTIARY),
        ];
    }

    public function cssVariableString(?TenantAdminSetting $settings = null): string
    {
        $p = $this->palette($settings);

        return sprintf(
            '--portal-primary:%1$s;--portal-secondary:%2$s;--portal-tertiary:%3$s;--color-primary:%1$s;--color-secondary:%2$s;--color-tertiary:%3$s;',
            $p['primary'],
            $p['secondary'],
            $p['tertiary']
        );
    }

    private function normalizeHex(?string $raw, string $fallback): string
    {
        if ($raw === null || trim($raw) === '') {
            return $fallback;
        }

        $hex = strtolower(trim($raw));
        if (preg_match('/^#([a-f0-9]{6}|[a-f0-9]{3})$/', $hex)) {
            if (strlen($hex) === 4) {
                return sprintf(
                    '#%s%s%s%s%s%s',
                    $hex[1],
                    $hex[1],
                    $hex[2],
                    $hex[2],
                    $hex[3],
                    $hex[3]
                );
            }

            return $hex;
        }

        return $fallback;
    }
}
