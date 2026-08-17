<?php

namespace App\Http\Requests\Escola;

use App\Models\Event;
use App\Rules\CpfRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $cpf = preg_replace('/\D/', '', (string) $this->input('palestrante_cpf', '')) ?: null;

        $this->merge([
            'allow_online_registration' => $this->boolean('allow_online_registration'),
            'com_certificado' => $this->boolean('com_certificado'),
            'chamada_georreferencia' => $this->boolean('chamada_georreferencia'),
            'registration_starts_at' => $this->filled('registration_starts_at') ? $this->input('registration_starts_at') : null,
            'registration_ends_at' => $this->filled('registration_ends_at') ? $this->input('registration_ends_at') : null,
            'certificado_disponivel_ate' => $this->filled('certificado_disponivel_ate') ? $this->input('certificado_disponivel_ate') : null,
            'latitude' => $this->filled('latitude') ? $this->input('latitude') : null,
            'longitude' => $this->filled('longitude') ? $this->input('longitude') : null,
            'geofence_raio_metros' => $this->filled('geofence_raio_metros') ? $this->input('geofence_raio_metros') : null,
            'presenca_inicio_em' => $this->filled('presenca_inicio_em') ? $this->input('presenca_inicio_em') : null,
            'presenca_fim_em' => $this->filled('presenca_fim_em') ? $this->input('presenca_fim_em') : null,
            'palestrante_nome' => $this->filled('palestrante_nome') ? trim((string) $this->input('palestrante_nome')) : null,
            'palestrante_cpf' => $cpf !== '' ? $cpf : null,
            'palestrante_senha' => $this->filled('palestrante_senha') ? $this->input('palestrante_senha') : null,
        ]);
    }

    public function rules(): array
    {
        $geo = $this->boolean('chamada_georreferencia');
        $hasSpeaker = filled($this->input('palestrante_nome'));
        /** @var Event|null $event */
        $event = $this->route('evento');
        $needsNewPassword = $hasSpeaker && ! filled($event?->palestrante_senha);

        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'allow_online_registration' => ['boolean'],
            'com_certificado' => ['boolean'],
            'chamada_georreferencia' => ['boolean'],
            'certificado_disponivel_ate' => ['nullable', 'date'],
            'latitude' => [Rule::requiredIf($geo), 'nullable', 'numeric', 'between:-90,90'],
            'longitude' => [Rule::requiredIf($geo), 'nullable', 'numeric', 'between:-180,180'],
            'geofence_raio_metros' => [Rule::requiredIf($geo), 'nullable', 'integer', 'min:10', 'max:5000'],
            'presenca_inicio_em' => [Rule::requiredIf($geo), 'nullable', 'date'],
            'presenca_fim_em' => array_values(array_filter([
                Rule::requiredIf($geo),
                'nullable',
                'date',
                $geo ? 'after:presenca_inicio_em' : null,
            ])),
            'palestrante_nome' => ['nullable', 'string', 'max:255'],
            'palestrante_cpf' => ['nullable', 'string', 'size:11', new CpfRule],
            'palestrante_senha' => [Rule::requiredIf($needsNewPassword), 'nullable', 'string', 'min:6', 'max:64'],
            'registration_starts_at' => [
                Rule::requiredIf($this->boolean('allow_online_registration')),
                'nullable',
                'date',
            ],
            'registration_ends_at' => array_values(array_filter([
                Rule::requiredIf($this->boolean('allow_online_registration')),
                'nullable',
                'date',
                $this->boolean('allow_online_registration') ? 'after:registration_starts_at' : null,
            ])),
            'max_seats' => ['nullable', 'integer', 'min:0'],
            'date_time' => ['required', 'date'],
            'zipcode' => ['nullable', 'string', 'max:9'],
            'address' => ['nullable', 'string', 'max:255'],
            'number' => ['nullable', 'string', 'max:20'],
            'complement' => ['nullable', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:2'],
            'photo' => ['nullable', 'image', 'max:4096'],
        ];
    }
}
