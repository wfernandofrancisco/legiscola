<?php

use App\Models\DirectorUf;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

function pwaTenant(string $slug = 'pwa-camara'): Tenant
{
    return Tenant::create([
        'name' => 'Câmara PWA',
        'nome_fantasia' => 'Câmara PWA',
        'slug' => $slug.'-'.uniqid(),
        'domain' => fake()->unique()->domainName(),
        'status' => Tenant::STATUS_ATIVO,
        'cadastro_status' => Tenant::CADASTRO_ATIVO,
        'estado' => 'SP',
    ]);
}

function pwaAluno(Tenant $tenant): User
{
    $user = User::create([
        'tenant_id' => $tenant->id,
        'name' => 'Aluno PWA',
        'email' => 'aluno-pwa-'.fake()->unique()->safeEmail(),
        'password' => Hash::make('password'),
        'user_type' => User::TYPE_TENANT_USER,
        'status' => User::STATUS_ATIVO,
        'email_verified_at' => now(),
    ]);
    $user->assignRole('tenant_user');

    return $user;
}

function pwaProfessor(Tenant $tenant): User
{
    $user = User::create([
        'tenant_id' => $tenant->id,
        'name' => 'Professor PWA',
        'email' => 'prof-pwa-'.fake()->unique()->safeEmail(),
        'password' => Hash::make('password'),
        'user_type' => User::TYPE_TENANT_MANAGER,
        'status' => User::STATUS_ATIVO,
        'email_verified_at' => now(),
    ]);
    $user->assignRole('tenant_manager');

    return $user;
}

function pwaDiretor(): User
{
    $user = User::create([
        'tenant_id' => null,
        'name' => 'Diretor PWA',
        'email' => 'diretor-pwa-'.fake()->unique()->safeEmail(),
        'password' => Hash::make('password'),
        'user_type' => User::TYPE_TENANT_DIRECTOR,
        'status' => User::STATUS_ATIVO,
        'email_verified_at' => now(),
    ]);
    $user->assignRole(User::TYPE_TENANT_DIRECTOR);

    DirectorUf::create([
        'user_id' => $user->id,
        'uf' => 'SP',
    ]);

    return $user;
}

it('serve o manifest PWA com start_url da área do aluno', function () {
    $tenant = pwaTenant();
    $host = $tenant->slug.'.'.config('app.domain');

    $this->get('http://'.$host.'/manifest.webmanifest?area=aluno')
        ->assertOk()
        ->assertHeader('content-type', 'application/manifest+json; charset=utf-8')
        ->assertJsonPath('start_url', '/aluno')
        ->assertJsonPath('display', 'standalone')
        ->assertJsonPath('theme_color', '#0f172a')
        ->assertJsonPath('name', 'Câmara PWA — Área do aluno')
        ->assertJsonFragment(['sizes' => '192x192']);
});

it('serve manifests distintos para diretor, professor e portal', function () {
    $tenant = pwaTenant();
    $host = $tenant->slug.'.'.config('app.domain');

    $this->get('http://'.$host.'/manifest.webmanifest?area=diretor')
        ->assertOk()
        ->assertJsonPath('start_url', '/diretor')
        ->assertJsonPath('theme_color', '#312e81');

    $this->get('http://'.$host.'/manifest.webmanifest?area=professor')
        ->assertOk()
        ->assertJsonPath('start_url', '/docente')
        ->assertJsonPath('theme_color', '#1e3a8a');

    $this->get('http://'.$host.'/manifest.webmanifest?area=portal')
        ->assertOk()
        ->assertJsonPath('start_url', '/')
        ->assertJsonPath('name', 'Câmara PWA — Portal');
});

it('serve o service worker e a página offline na raiz pública', function () {
    $tenant = pwaTenant();
    $host = $tenant->slug.'.'.config('app.domain');

    $this->get('http://'.$host.'/sw.js')
        ->assertOk()
        ->assertSee('legiscola-pwa-v2', false);

    $this->get('http://'.$host.'/offline.html')
        ->assertOk()
        ->assertSee('sem internet', false);
});

it('inclui meta tags e link do manifest na área do aluno', function () {
    $tenant = pwaTenant();
    $aluno = pwaAluno($tenant);
    $host = $tenant->slug.'.'.config('app.domain');

    $this->actingAs($aluno)
        ->get('http://'.$host.'/aluno')
        ->assertOk()
        ->assertSee('rel="manifest"', false)
        ->assertSee('area=aluno', false)
        ->assertSee('apple-mobile-web-app-capable', false)
        ->assertSee('theme-color', false)
        ->assertSee('data-pwa', false)
        ->assertSee('Instale a área do aluno', false);
});

it('inclui PWA no portal público da câmara', function () {
    $tenant = pwaTenant();
    $host = $tenant->slug.'.'.config('app.domain');

    $this->get('http://'.$host.'/')
        ->assertOk()
        ->assertSee('area=portal', false)
        ->assertSee('data-pwa-area="portal"', false);
});

it('inclui PWA na área do professor', function () {
    $tenant = pwaTenant();
    $professor = pwaProfessor($tenant);
    $host = $tenant->slug.'.'.config('app.domain');

    $this->actingAs($professor)
        ->get('http://'.$host.'/docente')
        ->assertOk()
        ->assertSee('area=professor', false)
        ->assertSee('data-pwa-area="professor"', false);
});

it('inclui PWA na área do diretor', function () {
    $diretor = pwaDiretor();

    $this->actingAs($diretor)
        ->get(route('diretor.dashboard'))
        ->assertOk()
        ->assertSee('area=diretor', false)
        ->assertSee('data-pwa-area="diretor"', false);
});
