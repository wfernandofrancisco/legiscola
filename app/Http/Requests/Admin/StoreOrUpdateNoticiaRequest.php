<?php

namespace App\Http\Requests\Admin;

use App\Models\Noticia;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrUpdateNoticiaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $noticia = $this->route('noticia');
        $noticiaId = $noticia?->id;
        $tipo = $this->input('tipo', Noticia::TIPO_COMPLETA);

        return [
            'titulo' => ['required', 'string', 'max:255'],
            'subtitulo' => ['nullable', 'string', 'max:255'],
            'tipo' => ['required', Rule::in([
                Noticia::TIPO_COMPLETA,
                Noticia::TIPO_RAPIDA,
                Noticia::TIPO_VIDEO,
            ])],
            'noticia' => [
                Rule::requiredIf($tipo === Noticia::TIPO_COMPLETA),
                'nullable',
                'string',
                'max:10000',
            ],
            'fonte_url' => [
                Rule::requiredIf($tipo === Noticia::TIPO_RAPIDA),
                'nullable',
                'url:http,https',
                'max:2048',
            ],
            'video_url' => [
                Rule::requiredIf($tipo === Noticia::TIPO_VIDEO),
                'nullable',
                'url:http,https',
                'max:2048',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (!$value) {
                        return;
                    }

                    $model = new Noticia(['video_url' => $value]);

                    if (!$model->youtube_video_id) {
                        $fail('Informe um link válido do YouTube.');
                    }
                },
            ],
            'tags' => ['nullable', 'string', 'max:255'],
            'foto_capa' => [
                Rule::requiredIf($tipo === Noticia::TIPO_RAPIDA && !$noticia?->foto_capa),
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:8192',
            ],
            'publicar_em' => ['nullable', 'date'],
            'is_destaque' => ['nullable', 'boolean'],
            'ativo' => ['required', Rule::in(['0', '1', 0, 1, true, false])],
            'fotos' => ['nullable', 'array', 'max:10'],
            'fotos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'legendas' => ['nullable', 'array'],
            'legendas.*' => ['nullable', 'string', 'max:120'],
            'delete_fotos' => ['nullable', 'array'],
            'delete_fotos.*' => ['integer', 'exists:noticia_fotos,id'],
            'slug' => ['nullable', Rule::unique('noticias', 'slug')->ignore($noticiaId)],
        ];
    }

    public function messages(): array
    {
        return [
            'titulo.required' => 'O titulo e obrigatorio.',
            'noticia.required' => 'O conteudo da noticia e obrigatorio.',
            'fonte_url.required' => 'Informe o link de origem da notícia rápida.',
            'fonte_url.url' => 'Informe um link de origem válido.',
            'video_url.required' => 'Informe o link do vídeo no YouTube.',
            'video_url.url' => 'Informe um link de vídeo válido.',
            'foto_capa.required' => 'A notícia rápida precisa de uma imagem de capa.',
            'fotos.*.image' => 'Cada arquivo deve ser uma imagem valida.',
            'fotos.*.mimes' => 'As fotos devem ser JPG, JPEG, PNG ou WEBP.',
            'fotos.*.max' => 'Cada foto pode ter no maximo 2MB.',
            'foto_capa.image' => 'A foto de capa deve ser uma imagem valida.',
            'foto_capa.mimes' => 'A foto de capa deve ser JPG, JPEG, PNG ou WEBP.',
            'foto_capa.max' => 'A foto de capa pode ter no maximo 8MB.',
            'fotos.max' => 'Voce pode enviar no maximo 10 fotos por vez.',
            'publicar_em.date' => 'Informe uma data de publicacao valida.',
        ];
    }
}
