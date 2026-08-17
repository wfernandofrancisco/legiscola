<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Support\Geo;
use App\Support\TenantUrl;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'certificado_disponivel_ate',
        'chamada_georreferencia',
        'latitude',
        'longitude',
        'geofence_raio_metros',
        'presenca_inicio_em',
        'presenca_fim_em',
        'palestrante_nome',
        'palestrante_cpf',
        'palestrante_senha',
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

    protected $hidden = [
        'palestrante_senha',
    ];

    protected function casts(): array
    {
        return [
            'allow_online_registration' => 'boolean',
            'com_certificado' => 'boolean',
            'chamada_georreferencia' => 'boolean',
            'certificado_disponivel_ate' => 'datetime',
            'presenca_inicio_em' => 'datetime',
            'presenca_fim_em' => 'datetime',
            'registration_starts_at' => 'datetime',
            'registration_ends_at' => 'datetime',
            'date_time' => 'datetime',
            'latitude' => 'float',
            'longitude' => 'float',
            'geofence_raio_metros' => 'integer',
        ];
    }

    public function hasSpeakerCertificateSetup(): bool
    {
        return filled($this->palestrante_nome) && filled($this->palestrante_senha);
    }

    public function speakerCertificatePublicUrl(): string
    {
        $this->loadMissing('tenant');

        return rtrim(TenantUrl::baseUrlForTenant($this->tenant), '/')
            .'/eventos/'.$this->id.'/certificado-palestrante';
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Aluno pode baixar o certificado deste evento até a data limite (se definida).
     */
    public function isCertificateAccessOpen(?DateTimeInterface $at = null): bool
    {
        if (! $this->certificado_disponivel_ate) {
            return true;
        }

        $at = $at ? CarbonImmutable::instance($at) : CarbonImmutable::now();

        return $at->lte($this->certificado_disponivel_ate);
    }

    public function isGeofenceCheckInEnabled(): bool
    {
        return $this->chamada_georreferencia
            && $this->latitude !== null
            && $this->longitude !== null
            && $this->geofence_raio_metros !== null
            && $this->geofence_raio_metros > 0
            && $this->presenca_inicio_em
            && $this->presenca_fim_em;
    }

    public function isPresenceWindowOpen(?DateTimeInterface $at = null): bool
    {
        if (! $this->presenca_inicio_em || ! $this->presenca_fim_em) {
            return false;
        }

        $at = $at ? CarbonImmutable::instance($at) : CarbonImmutable::now();

        return $at->gte($this->presenca_inicio_em) && $at->lte($this->presenca_fim_em);
    }

    public function distanceFromMeters(float $latitude, float $longitude): ?float
    {
        if ($this->latitude === null || $this->longitude === null) {
            return null;
        }

        return Geo::distanceMeters(
            (float) $this->latitude,
            (float) $this->longitude,
            $latitude,
            $longitude
        );
    }

    public function isWithinGeofence(float $latitude, float $longitude): bool
    {
        if (! $this->isGeofenceCheckInEnabled()) {
            return false;
        }

        $distance = $this->distanceFromMeters($latitude, $longitude);

        return $distance !== null && $distance <= (float) $this->geofence_raio_metros;
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
