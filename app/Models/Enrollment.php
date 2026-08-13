<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Relation;

class Enrollment extends Model
{
    use BelongsToTenant;
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'student_id',
        'class_id',
        'course_class_id',
        'status',
        'observations',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function turma(): BelongsTo
    {
        return $this->belongsTo(Turma::class, 'class_id');
    }

    public function courseClass(): BelongsTo
    {
        return $this->belongsTo(CourseClass::class);
    }

    /**
     * Matrículas só com aluno do tenant indicado + eager load de aluno/usuário.
     *
     * O callback de {@see Builder::with()} recebe a relação (ex.: BelongsTo), não o Builder;
     * usa-se {@see Relation::getQuery()}. Remove-se só {@see TenantScope} nas subqueries do aluno
     * e exige-se `students.tenant_id = $tenantId` para não misturar tenants quando o contexto HTTP diverge.
     */
    public function scopeWithStudentForAttendanceSheet(Builder $query, int $tenantId): Builder
    {
        return $query
            ->whereHas(
                'student',
                fn (Builder $q) => $q->withoutGlobalScopes([TenantScope::class])->where('students.tenant_id', $tenantId)
            )
            ->with([
                'student' => function (Relation $relation) use ($tenantId): void {
                    $relation->getQuery()
                        ->withoutGlobalScopes([TenantScope::class])
                        ->where($relation->getRelated()->qualifyColumn('tenant_id'), $tenantId)
                        ->with('user');
                },
            ]);
    }
}
