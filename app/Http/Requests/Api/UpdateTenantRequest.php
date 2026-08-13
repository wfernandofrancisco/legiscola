<?php

namespace App\Http\Requests\Api;

use App\Enums\TenantModulosPlano;
use App\Models\Tenant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tenantId = $this->route('tenant');

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('tenants', 'slug')->ignore($tenantId), 'regex:/^[a-z0-9-]+$/'],
            'domain' => ['nullable', 'string', 'max:255', Rule::unique('tenants', 'domain')->ignore($tenantId)],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['sometimes', Rule::in([
                Tenant::STATUS_ATIVO,
                Tenant::STATUS_INATIVO,
                Tenant::STATUS_SUSPENSO,
            ])],
            'trial_ends_at' => ['nullable', 'date'],
            'subscription_expires_at' => ['nullable', 'date'],

            'razao_social' => ['sometimes', 'string', 'max:255'],
            'nome_fantasia' => ['nullable', 'string', 'max:255'],
            'cnpj' => [
                'sometimes',
                'string',
                'regex:/^\d{14}$/',
                Rule::unique('tenants', 'cnpj')->ignore($tenantId),
            ],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'website' => ['nullable', 'string', 'max:255'],
            'cep' => ['nullable', 'string', 'max:9'],
            'logradouro' => ['nullable', 'string', 'max:255'],
            'numero' => ['nullable', 'string', 'max:20'],
            'complemento' => ['nullable', 'string', 'max:255'],
            'bairro' => ['nullable', 'string', 'max:100'],
            'cidade' => ['nullable', 'string', 'max:100'],
            'estado' => ['nullable', 'string', 'size:2'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'codigo_ibge_municipio' => ['nullable', 'string', 'max:20'],
            'codigo_municipio_estban' => ['nullable', 'string', 'max:20'],
            'codigo_municipio_caged' => ['nullable', 'string', 'max:20'],
            'codigo_importacao_exportacao' => ['nullable', 'string', 'max:20'],
            'observacoes' => ['nullable', 'string'],
            'cadastro_status' => ['nullable', Rule::in([
                Tenant::CADASTRO_ATIVO,
                Tenant::CADASTRO_INATIVO,
                Tenant::CADASTRO_PENDENTE,
            ])],
            'modulos_plano' => ['nullable', Rule::enum(TenantModulosPlano::class)],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->cnpj) {
            $this->merge([
                'cnpj' => preg_replace('/\D/', '', $this->cnpj),
            ]);
        }
    }

    public function attributes(): array
    {
        return [
            'name' => 'nome',
            'slug' => 'slug',
            'domain' => 'domínio',
            'description' => 'descrição',
            'status' => 'status',
            'trial_ends_at' => 'fim do período de teste',
            'subscription_expires_at' => 'expiração da assinatura',
            'razao_social' => 'razão social',
            'nome_fantasia' => 'nome fantasia',
            'cnpj' => 'CNPJ',
            'codigo_ibge_municipio' => 'codigo IBGE do municipio',
            'codigo_municipio_estban' => 'codigo municipio ESTBAN',
            'codigo_municipio_caged' => 'codigo IBGE do municipio (Caged)',
            'codigo_importacao_exportacao' => 'codigo municipio Comex (CO_MUN)',
            'cadastro_status' => 'status do cadastro',
            'modulos_plano' => 'plano de módulos',
            'latitude' => 'latitude (centro do mapa admin)',
            'longitude' => 'longitude (centro do mapa admin)',
        ];
    }
}
