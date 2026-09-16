<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CatalogPromoContact extends Model
{
    protected $fillable = [
        'catalog_promo_id',
        'director_user_id',
        'tenant_id',
        'user_id',
        'nome',
        'whatsapp',
        'interesse',
    ];

    public function promo(): BelongsTo
    {
        return $this->belongsTo(CatalogPromo::class, 'catalog_promo_id');
    }

    public function director(): BelongsTo
    {
        return $this->belongsTo(User::class, 'director_user_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function whatsappDigits(): string
    {
        return (string) preg_replace('/\D+/', '', (string) $this->whatsapp);
    }

    public function whatsappLink(): string
    {
        $digits = $this->whatsappDigits();
        if ($digits === '') {
            return '#';
        }

        if (! str_starts_with($digits, '55') && (strlen($digits) === 10 || strlen($digits) === 11)) {
            $digits = '55'.$digits;
        }

        return 'https://wa.me/'.$digits;
    }
}
