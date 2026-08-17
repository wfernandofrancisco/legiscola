<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class CourseClass extends Model
{
    use BelongsToTenant;
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'course_id',
        'name',
        'tipo_turma',
        'max_seats',
        'enrollment_start',
        'enrollment_end',
        'certificado_disponivel_ate',
        'satisfaction_survey_id',
        'satisfaction_survey_required',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'enrollment_start' => 'datetime',
            'enrollment_end' => 'datetime',
            'certificado_disponivel_ate' => 'datetime',
            'satisfaction_survey_required' => 'boolean',
        ];
    }

    /**
     * Aluno pode baixar o certificado desta turma até a data limite (se definida).
     */
    public function isCertificateAccessOpen(?DateTimeInterface $at = null): bool
    {
        if (! $this->certificado_disponivel_ate) {
            return true;
        }

        $at = $at ? CarbonImmutable::instance($at) : CarbonImmutable::now();

        return $at->lte($this->certificado_disponivel_ate);
    }

    public function requiresSatisfactionSurvey(): bool
    {
        return $this->satisfaction_survey_required && filled($this->satisfaction_survey_id);
    }

    public function studentCompletedSatisfactionSurvey(int $studentId): bool
    {
        if (! $this->satisfaction_survey_id) {
            return true;
        }

        return SatisfactionSurveyResponse::query()
            ->where('satisfaction_survey_id', $this->satisfaction_survey_id)
            ->where('course_class_id', $this->id)
            ->where('student_id', $studentId)
            ->exists();
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function satisfactionSurvey(): BelongsTo
    {
        return $this->belongsTo(SatisfactionSurvey::class, 'satisfaction_survey_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'course_class_id');
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(ClassLesson::class);
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class);
    }

    public function linkedQuizzes(): BelongsToMany
    {
        return $this->belongsToMany(Quiz::class, 'course_class_quiz')
            ->withPivot(['tenant_id', 'is_active', 'opens_at', 'closes_at'])
            ->withTimestamps();
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(CourseClassSchedule::class);
    }

    public function announcements(): HasMany
    {
        return $this->hasMany(CourseClassAnnouncement::class);
    }

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(Teacher::class, 'course_class_teacher')
            ->withPivot(['tenant_id', 'sort_order'])
            ->orderByPivot('sort_order')
            ->withTimestamps();
    }
}
