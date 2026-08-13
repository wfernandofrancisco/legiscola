<?php

namespace App\Models;

use App\Enums\CertificateTipoEmissao;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CertificateTemplate extends Model
{
    use BelongsToTenant;
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'tipo_emissao',
        'engine',
        'html_template',
        'background_image_path',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'tipo_emissao' => CertificateTipoEmissao::class,
        ];
    }

    public static function latestActiveForEmission(CertificateTipoEmissao $tipo): ?self
    {
        return static::query()
            ->where('is_active', true)
            ->where('tipo_emissao', $tipo)
            ->latest('id')
            ->first();
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }
}
