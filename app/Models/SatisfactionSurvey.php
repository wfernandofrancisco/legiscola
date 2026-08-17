<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SatisfactionSurvey extends Model
{
    use BelongsToTenant;
    use HasFactory;

    public const TIPO_FREE_TEXT = 'free_text';

    public const TIPO_CHOICES = 'choices';

    protected $fillable = [
        'tenant_id',
        'title',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function questions(): HasMany
    {
        return $this->hasMany(SatisfactionSurveyQuestion::class)->orderBy('position')->orderBy('id');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(SatisfactionSurveyResponse::class);
    }

    public function courseClasses(): HasMany
    {
        return $this->hasMany(CourseClass::class, 'satisfaction_survey_id');
    }
}
