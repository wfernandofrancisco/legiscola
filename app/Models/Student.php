<?php

namespace App\Models;

use App\Enums\Escolaridade;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use BelongsToTenant;
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'email',
        'enrollment_number',
        'birth_date',
        'sexo',
        'cpf',
        'telefone',
        'celular',
        'cep',
        'logradouro',
        'numero',
        'bairro',
        'cidade',
        'uf',
        'latitude',
        'longitude',
        'profissao',
        'escolaridade',
        'photo_path',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'escolaridade' => Escolaridade::class,
        ];
    }

    public function user(): BelongsTo
    {
        /*
         * Sem isso, o TenantScope em User pode ocultar o dono do vínculo (ex.: contexto x FK
         * fora de sincronia), e a UI mostra nome vazio mesmo com users.name preenchido.
         * Remove todos os escopos globais do User (incl. soft delete) para o vínculo por user_id.
         */
        return $this->belongsTo(User::class)->withoutGlobalScopes();
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function eventEnrollments(): HasMany
    {
        return $this->hasMany(EventEnrollment::class);
    }

    public function quizAttempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }
}
