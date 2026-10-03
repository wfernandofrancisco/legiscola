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
