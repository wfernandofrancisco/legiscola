<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CatalogPromoDismissal extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'catalog_promo_id',
        'user_id',
        'promo_updated_at',
        'dismissed_at',
    ];

    protected function casts(): array
    {
        return [
            'promo_updated_at' => 'datetime',
            'dismissed_at' => 'datetime',
        ];
    }

    public function promo(): BelongsTo
    {
        return $this->belongsTo(CatalogPromo::class, 'catalog_promo_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
