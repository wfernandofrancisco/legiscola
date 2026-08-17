<?php

use App\Models\Noticia;

it('extrai o id e gera embed de links do YouTube', function (string $url) {
    $noticia = new Noticia(['video_url' => $url]);

    expect($noticia->youtube_video_id)->toBe('dQw4w9WgXcQ')
        ->and($noticia->youtube_embed_url)
        ->toBe('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ');
})->with([
    'link curto' => 'https://youtu.be/dQw4w9WgXcQ',
    'link padrão' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
    'embed' => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
    'shorts' => 'https://www.youtube.com/shorts/dQw4w9WgXcQ',
]);

it('rejeita links de vídeo fora do YouTube', function () {
    $noticia = new Noticia(['video_url' => 'https://example.com/video']);

    expect($noticia->youtube_video_id)->toBeNull()
        ->and($noticia->youtube_embed_url)->toBeNull();
});
