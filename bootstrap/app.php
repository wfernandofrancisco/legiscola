<?php

use App\Http\Middleware\ApplyTenantContextFromAuth;
use App\Http\Middleware\EnsureCentralAccess;
use App\Http\Middleware\EnsureHasTenant;
use App\Http\Middleware\EnsureResponsiblePortalAccess;
use App\Http\Middleware\EnsureTenantAccess;
use App\Http\Middleware\EnsureTenantPortalContext;
use App\Http\Middleware\EnsureDocentePortalAccess;
use App\Http\Middleware\EnsureAcceptedGlobalPrivacyTerm;
use App\Http\Middleware\EnsureResponsibleManagerAccess;
use App\Http\Middleware\EnsureUserType;
use App\Http\Middleware\SetTenantContext;
use App\Models\User;
use App\Support\TenantWebEntryUrls;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        /*
         * SetTenantContext precisa aplicar antes do SubstituteBindings para que TenantScope participe do
         * route model binding (turmas/quizzes/etc.); sem isso implicit binding pode rodar sem filtro de tenant.
         */
        $middleware->prependToPriorityList(SubstituteBindings::class, SetTenantContext::class);

        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('central') || $request->is('central/*')) {
                return route('central.login');
            }

            return route('tenant.login');
        });

        $middleware->redirectUsersTo(function (Request $request) {
            $user = $request->user();

            if (! $user) {
                return route('home');
            }

            if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
                return route('central.dashboard');
            }

            if ($user->hasTenantRole(User::TYPE_TENANT_ADMIN)) {
                return route('admin.dashboard');
            }

            if ($user->isTenantProfessor()) {
                return route('professor.dashboard');
            }

            if ($user->hasTenantRole(User::TYPE_TENANT_MANAGER) || $user->isTenantManager()) {
                return route('professor.dashboard');
            }

            return route('app.dashboard');
        });

        $middleware->alias([
            'user.type' => EnsureUserType::class,
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'tenant' => SetTenantContext::class,
            'has-tenant' => EnsureHasTenant::class,
            'central-access' => EnsureCentralAccess::class,
            'tenant-access' => EnsureTenantAccess::class,
            'tenant.api-context' => ApplyTenantContextFromAuth::class,
            'tenant.portal' => EnsureTenantPortalContext::class,
            'responsible.access' => EnsureResponsiblePortalAccess::class,
            'docente-portal' => EnsureDocentePortalAccess::class,
            'responsible-manager' => EnsureResponsibleManagerAccess::class,
            'accepted-privacy-term' => EnsureAcceptedGlobalPrivacyTerm::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->renderable(function (\Throwable $e, Request $request) {
            if ($e instanceof AuthorizationException) {
                return null;
            }

            if (! $e instanceof HttpExceptionInterface || $e->getStatusCode() !== 403) {
                return null;
            }

            $msg = (string) $e->getMessage();
            $redirect403Messages = [
                'Este endereço não corresponde à sua empresa.',
                'Tipo de usuário não autorizado para esta área.',
            ];

            if (! in_array($msg, $redirect403Messages, true)) {
                return null;
            }

            if (TenantWebEntryUrls::shouldSkipForbiddenRedirect($request)) {
                return null;
            }

            $user = Auth::guard('web')->user();
            $target = $user
                ? TenantWebEntryUrls::afterTenantWebLogout($user)
                : TenantWebEntryUrls::tenantPanelLoginAbsolute();

            return redirect()->away($target)
                ->with('warning', 'Você não tem permissão para acessar esta área.');
        });
    })->create();
