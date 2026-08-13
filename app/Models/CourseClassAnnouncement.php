<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseClassAnnouncement extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'course_class_id',
        'reference_date',
        'subject',
        'body',
        'channels',
        'consent_acknowledged',
        'created_by',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'reference_date' => 'date',
            'channels' => 'array',
            'consent_acknowledged' => 'boolean',
            'processed_at' => 'datetime',
        ];
    }

    public function courseClass(): BelongsTo
    {
        return $this->belongsTo(CourseClass::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(CourseClassAnnouncementDelivery::class);
    }
}
