<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventEnrollment extends Model
{
    use BelongsToTenant;
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'event_id',
        'student_id',
        'presente',
        'checkin_latitude',
        'checkin_longitude',
        'checkin_accuracy_metros',
        'checkin_em',
    ];

    protected function casts(): array
    {
        return [
            'presente' => 'boolean',
            'checkin_latitude' => 'float',
            'checkin_longitude' => 'float',
            'checkin_accuracy_metros' => 'integer',
            'checkin_em' => 'datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
