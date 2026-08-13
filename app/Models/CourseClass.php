<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
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
        'status',
    ];

    protected function casts(): array
    {
        return [
            'enrollment_start' => 'datetime',
            'enrollment_end' => 'datetime',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
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
