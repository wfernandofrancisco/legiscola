<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Support\TenantUrl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventPalestra extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'event_id',
        'ordem',
        'title',
        'date_time',
        'max_seats',
        'com_certificado',
        'palestrante_nome',
        'palestrante_cpf',
        'palestrante_senha',
    ];

    protected $hidden = [
        'palestrante_senha',
    ];

    protected function casts(): array
    {
        return [
            'date_time' => 'datetime',
            'com_certificado' => 'boolean',
            'ordem' => 'integer',
            'max_seats' => 'integer',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function enrollmentPalestras(): HasMany
    {
        return $this->hasMany(EventEnrollmentPalestra::class);
    }

    public function occupiedSeats(): int
    {
        if (array_key_exists('enrollment_palestras_count', $this->attributes)) {
            return (int) $this->attributes['enrollment_palestras_count'];
        }

        return $this->enrollmentPalestras()->count();
    }

    public function remainingSeats(): ?int
    {
        if ($this->max_seats === null) {
            return null;
        }

        return max(0, (int) $this->max_seats - $this->occupiedSeats());
    }

    public function hasVacancy(): bool
    {
        if ($this->max_seats === null) {
            return true;
        }

        return $this->occupiedSeats() < (int) $this->max_seats;
    }

    public function seatsLabel(): string
    {
        if ($this->max_seats === null) {
            return 'Vagas ilimitadas';
        }

        $restantes = $this->remainingSeats() ?? 0;
        if ($restantes === 0) {
            return 'Lotada ('.$this->max_seats.' '.($this->max_seats === 1 ? 'vaga' : 'vagas').')';
        }

        return $restantes.' '.($restantes === 1 ? 'vaga restante' : 'vagas restantes').' de '.$this->max_seats;
    }

    public function hasSpeakerCertificateSetup(): bool
    {
        return filled($this->palestrante_nome) && filled($this->palestrante_senha);
    }

    public function speakerCertificatePublicUrl(): string
    {
        $this->loadMissing('event.tenant');

        return rtrim(TenantUrl::baseUrlForTenant($this->event?->tenant), '/')
            .'/eventos/'.$this->event_id.'/palestras/'.$this->id.'/certificado-palestrante';
    }
}
