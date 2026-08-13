<?php

namespace App\Support;

final class YoutubeId
{
    public static function fromUrl(?string $url): ?string
    {
        if ($url === null || trim($url) === '') {
            return null;
        }

        $url = trim($url);

        if (preg_match('~youtu\.be/([A-Za-z0-9_-]{6,})~', $url, $m)) {
            return $m[1];
        }

        if (preg_match('~[?&]v=([A-Za-z0-9_-]{6,})~', $url, $m)) {
            return $m[1];
        }

        if (preg_match('~youtube\.com/embed/([A-Za-z0-9_-]{6,})~', $url, $m)) {
            return $m[1];
        }

        if (preg_match('~youtube\.com/shorts/([A-Za-z0-9_-]{6,})~', $url, $m)) {
            return $m[1];
        }

        return null;
    }

    public static function isYoutube(?string $url): bool
    {
        if ($url === null || trim($url) === '') {
            return false;
        }

        return (bool) preg_match('~youtube\.com|youtu\.be~i', $url);
    }
}
