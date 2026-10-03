<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventEnrollmentPalestra extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'event_enrollment_id',
        'event_palestra_id',
        'presente',
    ];

    protected function casts(): array
    {
        return [
            'presente' => 'boolean',
        ];
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(EventEnrollment::class, 'event_enrollment_id');
    }

    public function palestra(): BelongsTo
    {
        return $this->belongsTo(EventPalestra::class, 'event_palestra_id');
    }
}
