<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SatisfactionSurveyAnswer extends Model
{
    use BelongsToTenant;
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'satisfaction_survey_response_id',
        'satisfaction_survey_question_id',
        'satisfaction_survey_option_id',
        'free_text',
    ];

    public function response(): BelongsTo
    {
        return $this->belongsTo(SatisfactionSurveyResponse::class, 'satisfaction_survey_response_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(SatisfactionSurveyQuestion::class, 'satisfaction_survey_question_id');
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(SatisfactionSurveyOption::class, 'satisfaction_survey_option_id');
    }
}
