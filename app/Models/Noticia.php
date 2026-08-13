<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Noticia extends Model
{
    use BelongsToTenant;
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'titulo',
        'subtitulo',
        'slug',
        'noticia',
        'tags',
        'foto_capa',
        'publicar_em',
        'is_destaque',
        'ativo',
    ];

    protected $casts = [
        'publicar_em' => 'datetime',
        'is_destaque' => 'boolean',
        'ativo' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fotos(): HasMany
    {
        return $this->hasMany(NoticiaFoto::class)->orderBy('ordem');
    }

    public function getFotoCapaUrlAttribute(): ?string
    {
        if (!$this->foto_capa) {
            return null;
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->url($this->foto_capa);
    }
}
