<?php

namespace App\Support;

final class CertificatePdfStyle
{
    public static function fontFaceCss(): string
    {
        $path = resource_path('fonts/GreatVibes-Regular.ttf');
        if (! is_file($path)) {
            return '';
        }

        $normalized = str_replace('\\', '/', $path);
        $uri = str_starts_with($normalized, '/')
            ? 'file://'.$normalized
            : 'file:///'.$normalized;

        return "@font-face { font-family: 'GreatVibes'; font-style: normal; font-weight: 400; src: url('".$uri."') format('truetype'); }";
    }
}
