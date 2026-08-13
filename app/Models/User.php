<?php

namespace App\Models;

use App\Enums\UserStatus;
use App\Enums\UserType;
use App\Mail\PasswordResetMail;
use App\Mail\VerifyEmailMail;
use App\Models\Concerns\BelongsToTenant;
use App\Support\TenantUrl;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use BelongsToTenant;

    /** @use HasFactory<UserFactory> */
    use HasApiTokens;

    use HasFactory;
    use HasRoles;
    use LogsActivity;
    use Notifiable;
    use SoftDeletes;

    const TYPE_SUPER_ADMIN = 'super_admin';

    const TYPE_TENANT_ADMIN = 'tenant_admin';

    const TYPE_TENANT_MANAGER = 'tenant_manager';

    const TYPE_TENANT_USER = 'tenant_user';

    /** Docente / professor vinculado ao painel pedagógico (rotas /docente). */
    const TYPE_TENANT_RESPONSIBLE = 'tenant_responsible';

    const STATUS_ATIVO = 'ativo';

    const STATUS_INATIVO = 'inativo';

    const STATUS_PENDENTE = 'pendente';

    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'password',
        'phone',
        'cpf',
        'endereco_completo',
        'user_type',
        'avatar',
        'status',
        'last_login_at',
        'last_login_ip',
        'email_verified_at',
        'accepted_global_privacy_term_version',
        'accepted_global_privacy_term_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'accepted_global_privacy_term_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'user_type', 'status'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $eventName) => "Usuário {$this->name} foi {$eventName}")
            ->useLogName('users');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function empresaResponsibleClaims(): HasMany
    {
        return $this->hasMany(EmpresaResponsibleClaim::class);
    }

    public function empresaResponsibleAssignments(): HasMany
    {
        return $this->hasMany(EmpresaResponsibleAssignment::class);
    }

    public function orcamentoSolicitacoesComoSolicitante(): HasMany
    {
        return $this->hasMany(OrcamentoSolicitacao::class, 'user_id');
    }

    public function student(): HasOne
    {
        return $this->hasOne(Student::class);
    }

    public function teacher(): HasOne
    {
        return $this->hasOne(Teacher::class);
    }

    /**
     * Docente vinculado à turma (pivot course_class_teacher).
     */
    public function teachesCourseClass(CourseClass $courseClass): bool
    {
        $teacher = $this->teacher;
        if (! $teacher) {
            return false;
        }

        return $courseClass->teachers()->where('teachers.id', $teacher->id)->exists();
    }

    public function isTenantProfessor(): bool
    {
        if ($this->hasRole('tenant_professor')) {
            return true;
        }

        return $this->teacher()->exists();
    }

    /**
     * Acesso ao painel /docente (docente da escola OU gestor pedagógico do tenant).
     */
    public function accessesDocentePortal(): bool
    {
        return $this->isTenantProfessor()
            || $this->isTenantManager()
            || $this->hasTenantRole(self::TYPE_TENANT_MANAGER);
    }

    public function aulasMinistradas(): HasMany
    {
        return $this->hasMany(ClassSchedule::class, 'teacher_id');
    }

    public function isSuperAdmin(): bool
    {
        return $this->user_type === self::TYPE_SUPER_ADMIN;
    }

    public function isTenantAdmin(): bool
    {
        return $this->user_type === self::TYPE_TENANT_ADMIN;
    }

    public function isTenantManager(): bool
    {
        return $this->user_type === self::TYPE_TENANT_MANAGER;
    }

    public function isTenantUser(): bool
    {
        return $this->user_type === self::TYPE_TENANT_USER;
    }

    /**
     * Roles Spatie do tenant usam underscore (tenant_admin …) — {@see RolesAndPermissionsSeeder}.
     * Também aceita variantes antigas em hífen.
     */
    public function hasTenantRole(string $underscoreRoleName): bool
    {
        return $this->hasRole([
            $underscoreRoleName,
            str_replace('_', '-', $underscoreRoleName),
        ]);
    }

    public function getUserTypeLabelAttribute(): string
    {
        return UserType::tryFrom($this->user_type)?->label() ?? ucfirst(str_replace('_', ' ', $this->user_type));
    }

    public function getUserTypeColorAttribute(): string
    {
        return UserType::tryFrom($this->user_type)?->color() ?? 'gray';
    }

    public function getStatusLabelAttribute(): string
    {
        return UserStatus::tryFrom($this->status)?->label() ?? ucfirst($this->status);
    }

    public function getStatusColorAttribute(): string
    {
        return UserStatus::tryFrom($this->status)?->color() ?? 'gray';
    }

    public function getFormattedPhoneAttribute(): string
    {
        if (! $this->phone) {
            return '';
        }

        $phone = preg_replace('/\D/', '', $this->phone);

        if (strlen($phone) === 11) {
            // Celular: (11) 99999-9999
            return '('.substr($phone, 0, 2).') '.substr($phone, 2, 5).'-'.substr($phone, 7);
        } elseif (strlen($phone) === 10) {
            // Fixo: (11) 9999-9999
            return '('.substr($phone, 0, 2).') '.substr($phone, 2, 4).'-'.substr($phone, 6);
        }

        return $this->phone; // Retorna como está se não conseguir formatar
    }

    public function isAtivo(): bool
    {
        return $this->status === self::STATUS_ATIVO;
    }

    public function managesEmpresa(Empresa $empresa): bool
    {
        if ($this->hasTenantRole(self::TYPE_TENANT_ADMIN)) {
            return true;
        }

        return $this->empresaResponsibleAssignments()->where('empresa_id', $empresa->id)->exists();
    }

    /**
     * Empresas que este utilizador pode gerir no painel responsável (admin: todas do tenant; gestor: vínculos).
     */
    public function managedEmpresas(): EloquentCollection
    {
        $tenantId = $this->tenant_id;
        $orderSql = 'COALESCE(NULLIF(TRIM(nome_fantasia), ""), razao_social)';

        if ($this->hasTenantRole(self::TYPE_TENANT_ADMIN)) {
            return Empresa::query()
                ->where('tenant_id', $tenantId)
                ->orderByRaw($orderSql)
                ->get();
        }

        $ids = $this->empresaResponsibleAssignments()->pluck('empresa_id');
        if ($ids->isEmpty()) {
            return new EloquentCollection;
        }

        return Empresa::query()
            ->where('tenant_id', $tenantId)
            ->whereIn('id', $ids)
            ->orderByRaw($orderSql)
            ->get();
    }

    /**
     * Rota nomeada do painel principal deste utilizador (mantém o mesmo subdomínio com URL::forceRootUrl).
     */
    public function tenantHomeRouteName(): string
    {
        if ($this->isSuperAdmin()) {
            return 'central.dashboard';
        }
        if ($this->hasTenantRole(self::TYPE_TENANT_ADMIN)) {
            return 'admin.dashboard';
        }
        if ($this->isTenantProfessor()) {
            return 'professor.dashboard';
        }
        if ($this->hasTenantRole(self::TYPE_TENANT_MANAGER) || $this->isTenantManager()) {
            return 'professor.dashboard';
        }

        return 'app.dashboard';
    }

    public function sendEmailVerificationNotification(): void
    {
        // A assinatura inclui o host; tem de coincidir com o domínio onde o utilizador abre o link (subdomínio do tenant).
        $tenantRoot = rtrim(TenantUrl::baseUrlForUser($this), '/');
        URL::forceRootUrl($tenantRoot);

        try {
            $verificationUrl = URL::temporarySignedRoute(
                'verification.verify',
                now()->addMinutes(60),
                [
                    'id' => $this->getKey(),
                    'hash' => sha1($this->getEmailForVerification()),
                ],
                true
            );
        } finally {
            URL::forceRootUrl(null);
        }

        Mail::to($this->email)->send(
            new VerifyEmailMail($this, $verificationUrl)
        );
    }

    public function sendPasswordResetNotification($token): void
    {
        $resetUrl = TenantUrl::tenantRoute($this, 'password.reset', [
            'token' => $token,
            'email' => $this->email,
        ]);

        Mail::to($this->email)->queue(new PasswordResetMail($this, $resetUrl));
    }
}
