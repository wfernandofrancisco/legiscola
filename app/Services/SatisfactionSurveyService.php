<?php

namespace App\Services;

use App\Models\SatisfactionSurvey;
use App\Models\SatisfactionSurveyOption;
use App\Models\SatisfactionSurveyQuestion;
use App\Support\TenantContext;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class SatisfactionSurveyService
{
    public function paginateFiltered(int $perPage = 15, ?string $search = null, ?string $status = null): LengthAwarePaginator
    {
        return SatisfactionSurvey::query()
            ->withCount('questions')
            ->when($search, fn ($q) => $q->where('title', 'like', '%'.$search.'%'))
            ->when($status !== null && $status !== '', fn ($q) => $q->where('is_active', (bool) $status))
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): SatisfactionSurvey
    {
        return DB::transaction(function () use ($data): SatisfactionSurvey {
            $tenantId = (int) TenantContext::getTenantId();

            $survey = SatisfactionSurvey::query()->create([
                'tenant_id' => $tenantId,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'is_active' => (bool) ($data['is_active'] ?? true),
            ]);

            $this->syncQuestions($survey, $data['questions'] ?? [], $tenantId);

            return $survey->load('questions.options');
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(SatisfactionSurvey $survey, array $data): SatisfactionSurvey
    {
        return DB::transaction(function () use ($survey, $data): SatisfactionSurvey {
            $tenantId = (int) $survey->tenant_id;

            $survey->update([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'is_active' => (bool) ($data['is_active'] ?? false),
            ]);

            $survey->questions()->each(function (SatisfactionSurveyQuestion $question): void {
                $question->options()->delete();
            });
            $survey->questions()->delete();

            $this->syncQuestions($survey, $data['questions'] ?? [], $tenantId);

            return $survey->fresh(['questions.options']);
        });
    }

    public function delete(SatisfactionSurvey $survey): void
    {
        $survey->delete();
    }

    /**
     * @param  list<array<string, mixed>>  $questions
     */
    private function syncQuestions(SatisfactionSurvey $survey, array $questions, int $tenantId): void
    {
        foreach (array_values($questions) as $index => $questionData) {
            $tipo = (string) ($questionData['tipo'] ?? SatisfactionSurvey::TIPO_FREE_TEXT);

            $question = SatisfactionSurveyQuestion::query()->create([
                'tenant_id' => $tenantId,
                'satisfaction_survey_id' => $survey->id,
                'question' => (string) $questionData['question'],
                'tipo' => $tipo,
                'position' => $index,
            ]);

            if ($tipo !== SatisfactionSurvey::TIPO_CHOICES) {
                continue;
            }

            $options = array_values(array_filter(
                $questionData['options'] ?? [],
                fn ($opt) => filled(is_array($opt) ? ($opt['label'] ?? null) : $opt)
            ));

            foreach ($options as $optIndex => $option) {
                $label = is_array($option) ? (string) ($option['label'] ?? '') : (string) $option;

                SatisfactionSurveyOption::query()->create([
                    'tenant_id' => $tenantId,
                    'satisfaction_survey_question_id' => $question->id,
                    'label' => $label,
                    'position' => $optIndex,
                ]);
            }
        }
    }
}
