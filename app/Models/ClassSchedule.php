<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassSchedule extends Model
{
    use BelongsToTenant;
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'class_id',
        'subject_id',
        'teacher_id',
        'date',
        'start_time',
        'end_time',
        'is_online',
        'meeting_url',
        'material_url',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_online' => 'boolean',
        ];
    }

    public function turma(): BelongsTo
    {
        return $this->belongsTo(Turma::class, 'class_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }
}
