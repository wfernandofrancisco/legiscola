<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use BelongsToTenant;
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'title',
        'description',
        'allow_online_registration',
        'com_certificado',
        'registration_starts_at',
        'registration_ends_at',
        'max_seats',
        'date_time',
        'zipcode',
        'address',
        'number',
        'complement',
        'district',
        'city',
        'state',
        'photo_path',
    ];

    protected function casts(): array
    {
        return [
            'allow_online_registration' => 'boolean',
            'com_certificado' => 'boolean',
            'registration_starts_at' => 'datetime',
            'registration_ends_at' => 'datetime',
            'date_time' => 'datetime',
        ];
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(EventEnrollment::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    /**
     * Inscrição online permitida neste momento (período + evento futuro).
     */
    public function isOnlineRegistrationOpen(?DateTimeInterface $at = null): bool
    {
        if (! $this->allow_online_registration) {
            return false;
        }

        $at = $at ? CarbonImmutable::instance($at) : CarbonImmutable::now();

        if ($this->date_time->lt($at)) {
            return false;
        }

        if ($this->registration_starts_at && $at->lt($this->registration_starts_at)) {
            return false;
        }

        if ($this->registration_ends_at && $at->gt($this->registration_ends_at)) {
            return false;
        }

        return true;
    }

    public function hasVacancyForEnrollment(): bool
    {
        if ($this->max_seats === null) {
            return true;
        }

        return $this->enrollments()->count() < (int) $this->max_seats;
    }
}
