<?php

$src = dirname(__DIR__).'/public/img/logo-curto.png';
$im = imagecreatefrompng($src);
if (! $im) {
    fwrite(STDERR, "Não foi possível ler {$src}\n");
    exit(1);
}

function makeIcon($srcIm, int $size, string $out, float $padRatio = 0.0): void
{
    $canvas = imagecreatetruecolor($size, $size);
    imagesavealpha($canvas, true);

    if ($padRatio > 0) {
        imagealphablending($canvas, true);
        $bg = imagecolorallocate($canvas, 15, 23, 42);
        imagefilledrectangle($canvas, 0, 0, $size, $size, $bg);
    } else {
        imagealphablending($canvas, false);
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefilledrectangle($canvas, 0, 0, $size, $size, $transparent);
        imagealphablending($canvas, true);
    }

    $inner = (int) round($size * (1 - 2 * $padRatio));
    $offset = (int) round(($size - $inner) / 2);
    $sw = imagesx($srcIm);
    $sh = imagesy($srcIm);
    $scale = min($inner / $sw, $inner / $sh);
    $dw = (int) round($sw * $scale);
    $dh = (int) round($sh * $scale);
    $dx = $offset + (int) round(($inner - $dw) / 2);
    $dy = $offset + (int) round(($inner - $dh) / 2);
    imagecopyresampled($canvas, $srcIm, $dx, $dy, 0, 0, $dw, $dh, $sw, $sh);
    imagepng($canvas, $out);
    imagedestroy($canvas);
    echo $out." OK\n";
}

$base = dirname(__DIR__).'/public/img';
makeIcon($im, 192, $base.'/pwa-icon-192.png');
makeIcon($im, 512, $base.'/pwa-icon-512.png');
makeIcon($im, 512, $base.'/pwa-maskable-512.png', 0.12);
imagedestroy($im);
