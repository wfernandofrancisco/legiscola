<?php

namespace App\Models;

use App\Enums\TenantModulosPlano;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Tenant extends Model
{
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;

    const STATUS_ATIVO = 'ativo';

    const STATUS_INATIVO = 'inativo';

    const STATUS_SUSPENSO = 'suspenso';

    const CADASTRO_ATIVO = 'ativo';

    const CADASTRO_INATIVO = 'inativo';

    const CADASTRO_PENDENTE = 'pendente';

    protected $fillable = [
        'name',
        'slug',
        'domain',
        'description',
        'status',
        'trial_ends_at',
        'subscription_expires_at',
        'razao_social',
        'nome_fantasia',
        'cnpj',
        'contact_email',
        'phone',
        'website',
        'cep',
        'logradouro',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'portal_nome_cidade',
        'estado',
        'latitude',
        'longitude',
        'codigo_ibge_municipio',
        'observacoes',
        'cadastro_status',
        'modulos_plano',
    ];

    protected function casts(): array
    {
        return [
            'trial_ends_at' => 'datetime',
            'subscription_expires_at' => 'datetime',
            'latitude' => 'float',
            'longitude' => 'float',
            'modulos_plano' => TenantModulosPlano::class,
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'slug', 'status', 'cadastro_status'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $eventName) => "Tenant {$this->name} foi {$eventName}")
            ->useLogName('tenants');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class);
    }

    public function empresas(): HasMany
    {
        return $this->hasMany(Empresa::class);
    }

    public function adminSetting(): HasOne
    {
        return $this->hasOne(TenantAdminSetting::class);
    }

    /**
     * Nome amigável para exibição (fantasia ou razão ou name).
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->nome_fantasia
            ?: $this->razao_social
            ?: $this->name;
    }

    /**
     * Título exibido no portal público (ex.: "Legiscola Araras" quando portal_nome_cidade = "Araras").
     * Usa APP_NAME (padrão Legiscola no .env).
     */
    public function portalBrandTitle(): string
    {
        $app = trim((string) config('app.name', 'Legiscola'));
        $cidade = trim((string) ($this->portal_nome_cidade ?? ''));
        if ($cidade !== '') {
            return $app !== '' ? $app.' '.$cidade : $cidade;
        }

        return (string) ($this->nome_fantasia ?: $this->razao_social ?: $this->name);
    }

    /**
     * Linha institucional sem repetir APP_NAME — para o navbar quando já existe logo Legiscola.
     */
    public function portalChamberBrandLine(): string
    {
        $full = trim($this->portalBrandTitle());
        $app = trim((string) config('app.name', ''));

        if ($app !== '' && str_starts_with($full, $app.' ')) {
            $rest = trim(mb_substr($full, mb_strlen($app.' ')));

            return $rest !== '' ? $rest : (string) $this->display_name;
        }

        if ($app !== '' && $full === $app) {
            return (string) $this->display_name;
        }

        return $full;
    }

    /** Iniciais curtas para ícones do navbar (2 caracteres). */
    public function portalBrandInitials(): string
    {
        $title = $this->portalBrandTitle();

        return mb_strtoupper(mb_strlen($title) >= 2 ? mb_substr($title, 0, 2) : $title);
    }
}
