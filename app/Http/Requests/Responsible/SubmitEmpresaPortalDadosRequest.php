<?php

namespace App\Http\Requests\Responsible;

use App\Http\Requests\Admin\UpdateEmpresaOverrideRequest;
use Illuminate\Foundation\Http\FormRequest;

/** Mesmos campos {@see UpdateEmpresaOverrideRequest}. */
class SubmitEmpresaPortalDadosRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'nome_fantasia' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'whatsapp' => ['nullable', 'string', 'max:20'],
            'telefone' => ['nullable', 'string', 'max:20'],
            'site_url' => ['nullable', 'string', 'max:512'],
            'facebook_url' => ['nullable', 'string', 'max:512'],
            'instagram_url' => ['nullable', 'string', 'max:512'],
            'tiktok_url' => ['nullable', 'string', 'max:512'],
            'twitter_x_url' => ['nullable', 'string', 'max:512'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
            'remover_logo' => ['nullable', 'boolean'],
            'tipo_logradouro' => ['nullable', 'string', 'max:255'],
            'logradouro' => ['nullable', 'string', 'max:255'],
            'numero' => ['nullable', 'string', 'max:20'],
            'complemento' => ['nullable', 'string', 'max:255'],
            'bairro' => ['nullable', 'string', 'max:255'],
            'cep' => ['nullable', 'string', 'max:8'],
            'uf' => ['nullable', 'string', 'size:2'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'deseja_receber_orcamentos' => ['nullable', 'boolean'],
        ];
    }
}
