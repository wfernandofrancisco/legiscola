<?php

namespace App\Http\Requests\Admin;

use App\Models\SatisfactionSurvey;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreSatisfactionSurveyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'description' => $this->filled('description') ? $this->input('description') : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['boolean'],
            'questions' => ['required', 'array', 'min:1'],
            'questions.*.question' => ['required', 'string', 'max:500'],
            'questions.*.tipo' => ['required', Rule::in([SatisfactionSurvey::TIPO_FREE_TEXT, SatisfactionSurvey::TIPO_CHOICES])],
            'questions.*.options' => ['nullable', 'array'],
            'questions.*.options.*.label' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v): void {
            foreach ((array) $this->input('questions', []) as $index => $question) {
                if (($question['tipo'] ?? null) !== SatisfactionSurvey::TIPO_CHOICES) {
                    continue;
                }

                $labels = collect($question['options'] ?? [])
                    ->map(fn ($opt) => trim((string) (is_array($opt) ? ($opt['label'] ?? '') : $opt)))
                    ->filter()
                    ->values();

                if ($labels->count() < 2) {
                    $v->errors()->add(
                        "questions.$index.options",
                        'Perguntas de opções precisam ter pelo menos 2 alternativas preenchidas.'
                    );
                }
            }
        });
    }
}
