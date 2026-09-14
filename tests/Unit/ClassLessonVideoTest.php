<?php

use App\Models\ClassLesson;
use App\Support\VideoEmbed;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

uses(TestCase::class);

it('marca arquivo anexado como vídeo nativo e prioriza sobre url', function () {
    $lesson = new ClassLesson([
        'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        'video_path' => 'class-lessons/1/videos/aula.mp4',
    ]);

    expect($lesson->isUploadedVideo())->toBeTrue()
        ->and($lesson->effectiveVideoIsNative())->toBeTrue()
        ->and($lesson->effectiveVideoSourceLabel())->toBe('Arquivo anexado')
        ->and($lesson->effectiveVideoUrl())->toContain('class-lessons/1/videos/aula.mp4');
});

it('identifica youtube quando só há link', function () {
    $lesson = new ClassLesson([
        'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        'video_path' => null,
    ]);

    expect($lesson->effectiveVideoIsNative())->toBeFalse()
        ->and($lesson->effectiveVideoSourceLabel())->toBe('YouTube')
        ->and(VideoEmbed::embedUrl($lesson->effectiveVideoUrl()))->toContain('youtube-nocookie.com/embed/');
});

it('aceita video_file até 200mb na validação de aula', function () {
    $file = UploadedFile::fake()->create('aula.mp4', 180 * 1024, 'video/mp4');

    $request = new \App\Http\Requests\Escola\StoreClassLessonRequest;
    $rules = $request->rules();

    expect($rules['video_file'])->toContain('max:204800')
        ->and($rules['video_file'])->toContain('mimetypes:video/mp4,video/webm,video/quicktime');

    $validator = validator(['video_file' => $file], ['video_file' => $rules['video_file']]);
    expect($validator->fails())->toBeFalse();
});
