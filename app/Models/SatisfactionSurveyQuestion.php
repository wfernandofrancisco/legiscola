<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SatisfactionSurveyQuestion extends Model
{
    use BelongsToTenant;
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'satisfaction_survey_id',
        'question',
        'tipo',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
        ];
    }

    public function survey(): BelongsTo
    {
        return $this->belongsTo(SatisfactionSurvey::class, 'satisfaction_survey_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(SatisfactionSurveyOption::class)->orderBy('position')->orderBy('id');
    }

    public function isFreeText(): bool
    {
        return $this->tipo === SatisfactionSurvey::TIPO_FREE_TEXT;
    }

    public function isChoices(): bool
    {
        return $this->tipo === SatisfactionSurvey::TIPO_CHOICES;
    }
}
