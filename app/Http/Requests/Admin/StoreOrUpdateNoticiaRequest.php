<?php

namespace App\Http\Requests\Admin;

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
        $noticiaId = $this->route('noticia')?->id;

        return [
            'titulo' => ['required', 'string', 'max:255'],
            'subtitulo' => ['nullable', 'string', 'max:255'],
            'noticia' => ['required', 'string', 'max:10000'],
            'tags' => ['nullable', 'string', 'max:255'],
            'foto_capa' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
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
            'fotos.*.image' => 'Cada arquivo deve ser uma imagem valida.',
            'fotos.*.mimes' => 'As fotos devem ser JPG, JPEG, PNG ou WEBP.',
            'fotos.*.max' => 'Cada foto pode ter no maximo 2MB.',
            'foto_capa.image' => 'A foto de capa deve ser uma imagem valida.',
            'foto_capa.mimes' => 'A foto de capa deve ser JPG, JPEG, PNG ou WEBP.',
            'foto_capa.max' => 'A foto de capa pode ter no maximo 3MB.',
            'fotos.max' => 'Voce pode enviar no maximo 10 fotos por vez.',
            'publicar_em.date' => 'Informe uma data de publicacao valida.',
        ];
    }
}
