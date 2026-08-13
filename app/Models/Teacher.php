<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use BelongsToTenant;
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'full_name',
        'email',
        'phone',
        'photo_path',
        'status',
        'bio',
        'specialities',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'string',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withoutGlobalScopes();
    }

    public function courseClasses(): BelongsToMany
    {
        return $this->belongsToMany(CourseClass::class, 'course_class_teacher')
            ->withPivot(['tenant_id', 'sort_order'])
            ->orderByPivot('sort_order')
            ->withTimestamps();
    }
}
