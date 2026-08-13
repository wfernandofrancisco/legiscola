<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Turma extends Model
{
    use BelongsToTenant;
    use HasFactory;

    protected $table = 'classes';

    protected $fillable = [
        'tenant_id',
        'course_id',
        'name',
        'max_seats',
        'enrollment_start',
        'enrollment_end',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'enrollment_start' => 'date',
            'enrollment_end' => 'date',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'class_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(ClassSchedule::class, 'class_id');
    }

    public function examTemplates(): HasMany
    {
        return $this->hasMany(ExamTemplate::class, 'class_id');
    }
}
