<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class TenantAdminSetting extends Model
{
    use BelongsToTenant;

    protected $table = 'tenant_admin_settings';

    protected $fillable = [
        'tenant_id',
        'whatsapp',
        'email',
        'nome_camara',
        'cep',
        'logradouro',
        'numero',
        'bairro',
        'uf',
        'cidade',
        'horario_funcionamento',
        'instagram',
        'x',
        'facebook',
        'telefone',
        'logo_prefeitura_path',
        'foto_capa_path',
        'primary_color',
        'secondary_color',
        'tertiary_color',
    ];

    protected function casts(): array
    {
        return [];
    }
}
