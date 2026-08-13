<?php

namespace App\Providers;

use App\Models\Cnae;
use App\Models\Certificate;
use App\Models\CourseClass;
use App\Models\EmpresaOverride;
use App\Models\EmpresaRelacao;
use App\Models\EmpresaRelacaoArquivo;
use App\Models\EmpresaRelacaoComentario;
use App\Models\Grade;
use App\Models\Attendance;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Quiz;
use App\Policies\CnaePolicy;
use App\Policies\CertificatePolicy;
use App\Policies\CourseClassPolicy;
use App\Policies\EmpresaOverridePolicy;
use App\Policies\EmpresaRelacaoArquivoPolicy;
use App\Policies\EmpresaRelacaoComentarioPolicy;
use App\Policies\EmpresaRelacaoPolicy;
use App\Policies\GradePolicy;
use App\Policies\AttendancePolicy;
use App\Policies\PermissionPolicy;
use App\Policies\RolePolicy;
use App\Policies\TenantPolicy;
use App\Policies\UserPolicy;
use App\Policies\QuizPolicy;
use App\Support\TenantUrl;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Tenant::class => TenantPolicy::class,
        User::class => UserPolicy::class,
        Role::class => RolePolicy::class,
        Permission::class => PermissionPolicy::class,
        Cnae::class => CnaePolicy::class,
        EmpresaRelacao::class => EmpresaRelacaoPolicy::class,
        EmpresaRelacaoComentario::class => EmpresaRelacaoComentarioPolicy::class,
        EmpresaRelacaoArquivo::class => EmpresaRelacaoArquivoPolicy::class,
        EmpresaOverride::class => EmpresaOverridePolicy::class,
        Grade::class => GradePolicy::class,
        Attendance::class => AttendancePolicy::class,
        Certificate::class => CertificatePolicy::class,
        CourseClass::class => CourseClassPolicy::class,
        Quiz::class => QuizPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        VerifyEmail::createUrlUsing(function (User $notifiable): string {
            $verificationUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                'verification.verify',
                now()->addMinutes(60),
                [
                    'id' => $notifiable->getKey(),
                    'hash' => sha1($notifiable->getEmailForVerification()),
                ],
                false
            );

            return TenantUrl::onTenant($notifiable, $verificationUrl);
        });

        ResetPassword::createUrlUsing(function (User $user, string $token): string {
            return TenantUrl::tenantRoute($user, 'password.reset', [
                'token' => $token,
                'email' => $user->email,
            ]);
        });
    }
}