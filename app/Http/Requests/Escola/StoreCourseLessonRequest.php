<?php

namespace App\Http\Requests\Escola;

use Illuminate\Foundation\Http\FormRequest;

class StoreCourseLessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'video_url' => ['nullable', 'url', 'max:2000'],
            'video_file' => ['nullable', 'file', 'max:204800', 'mimetypes:video/mp4,video/webm,video/quicktime'],
            'remove_video' => ['nullable', 'boolean'],
            'material_url' => ['nullable', 'url', 'max:2000'],
            'material_file' => [
                'nullable',
                'file',
                'max:20480',
                'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip,txt,png,jpg,jpeg,webp',
            ],
            'remove_material' => ['nullable', 'boolean'],
            'ordem' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'video_file.max' => 'O vídeo pode ter no máximo 200 MB.',
            'video_file.mimetypes' => 'Envie um vídeo MP4, WebM ou MOV.',
        ];
    }
}
