<?php

namespace App\Support;

use App\Enums\CatalogVideoProvider;

/**
 * Reconhece o provedor de um link de vídeo e monta a URL de embed.
 *
 * O sistema já embedava YouTube ({@see YoutubeId}); aqui o Vimeo entra junto, e links
 * de outros provedores caem em "externo" (abrem em nova aba em vez de embedar).
 */
final class VideoEmbed
{
    public static function detectProvider(?string $url): ?CatalogVideoProvider
    {
        if ($url === null || trim($url) === '') {
            return null;
        }

        if (YoutubeId::isYoutube($url)) {
            return CatalogVideoProvider::Youtube;
        }

        if (self::vimeoId($url) !== null) {
            return CatalogVideoProvider::Vimeo;
        }

        return CatalogVideoProvider::Externo;
    }

    public static function vimeoId(?string $url): ?string
    {
        if ($url === null || trim($url) === '') {
            return null;
        }

        if (preg_match('~vimeo\.com/(?:video/)?(\d{6,})~i', trim($url), $m)) {
            return $m[1];
        }

        return null;
    }

    /**
     * URL pronta para <iframe>, ou null quando o link não é embedável.
     */
    public static function embedUrl(?string $url): ?string
    {
        $provider = self::detectProvider($url);

        if ($provider === CatalogVideoProvider::Youtube) {
            $id = YoutubeId::fromUrl($url);

            return $id ? 'https://www.youtube-nocookie.com/embed/'.$id : null;
        }

        if ($provider === CatalogVideoProvider::Vimeo) {
            $id = self::vimeoId($url);

            return $id ? 'https://player.vimeo.com/video/'.$id : null;
        }

        return null;
    }

    public static function isEmbeddable(?string $url): bool
    {
        return self::embedUrl($url) !== null;
    }
}
