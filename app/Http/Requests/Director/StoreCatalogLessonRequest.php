<?php

namespace App\Http\Requests\Director;

use Illuminate\Foundation\Http\FormRequest;

class StoreCatalogLessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:255'],
            'descricao' => ['nullable', 'string', 'max:5000'],
            'video_url' => ['nullable', 'url', 'max:2000'],
            // 200 MB — arquivos de aula costumam ser grandes; o PHP do Laragon local já aceita.
            'video_file' => ['nullable', 'file', 'max:204800', 'mimetypes:video/mp4,video/webm,video/quicktime'],
            'remove_video' => ['nullable', 'boolean'],
            'video_duracao_segundos' => ['nullable', 'integer', 'min:1', 'max:86400'],
            'material_url' => ['nullable', 'url', 'max:2000'],
            'material_file' => ['nullable', 'file', 'max:20480', 'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip,jpg,jpeg,png'],
            'remove_material' => ['nullable', 'boolean'],
            'ordem' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function attributes(): array
    {
        return [
            'titulo' => 'título da aula',
            'descricao' => 'descrição',
            'video_url' => 'link do vídeo',
            'video_file' => 'arquivo de vídeo',
            'video_duracao_segundos' => 'duração do vídeo',
            'material_url' => 'link do material',
            'material_file' => 'arquivo de material',
        ];
    }

    public function messages(): array
    {
        return [
            'video_file.max' => 'O vídeo pode ter no máximo 200 MB.',
            'video_file.mimetypes' => 'Envie um vídeo em MP4, WebM ou MOV.',
        ];
    }
}
