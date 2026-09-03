<?php

use App\Models\Event;
use App\Models\EventEnrollment;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->withoutMiddleware(PreventRequestForgery::class);
});

function evpTenantHost(Tenant $tenant): string
{
    return $tenant->slug.'.'.config('app.domain');
}

function evpCreateTenant(): Tenant
{
    return Tenant::create([
        'name' => 'Câmara Teste',
        'slug' => 'evp-'.uniqid(),
        'domain' => fake()->unique()->domainName(),
        'status' => Tenant::STATUS_ATIVO,
        'cadastro_status' => Tenant::CADASTRO_ATIVO,
    ]);
}

function evpMakeAdmin(Tenant $tenant): User
{
    $user = User::create([
        'tenant_id' => $tenant->id,
        'name' => 'Admin Evento',
        'email' => 'admin-evp-'.fake()->unique()->safeEmail(),
        'password' => Hash::make('password'),
        'user_type' => User::TYPE_TENANT_ADMIN,
        'status' => User::STATUS_ATIVO,
        'email_verified_at' => now(),
    ]);
    $user->assignRole('tenant_admin');

    return $user;
}

function evpCreateEvent(Tenant $tenant, ?int $maxSeats = null): Event
{
    return Event::forceCreate([
        'tenant_id' => $tenant->id,
        'title' => 'Sessão solene',
        'description' => null,
        'allow_online_registration' => false,
        'max_seats' => $maxSeats,
        'date_time' => now()->addDays(3),
        'city' => 'Araras',
    ]);
}

function evpParticipantPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Maria da Silva',
        'email' => 'maria-evp-'.fake()->unique()->safeEmail(),
        'cpf' => '52998224725',
        'birth_date' => '1990-05-20',
        'sexo' => 'feminino',
        'cidade' => 'Araras',
        'presente' => '1',
    ], $overrides);
}

it('admin inscreve participante novo no evento e cria usuário', function () {
    $tenant = evpCreateTenant();
    $admin = evpMakeAdmin($tenant);
    $event = evpCreateEvent($tenant);
    $host = evpTenantHost($tenant);
    $payload = evpParticipantPayload();

    $this->actingAs($admin)
        ->post('http://'.$host.'/admin/escola/eventos/'.$event->id.'/inscricoes', $payload)
        ->assertRedirect()
        ->assertSessionHas('success');

    $user = User::query()->where('email', $payload['email'])->first();
    expect($user)->not->toBeNull()
        ->and($user->user_type)->toBe(User::TYPE_TENANT_USER);

    $student = Student::query()->where('email', $payload['email'])->first();
    expect($student)->not->toBeNull();

    expect(EventEnrollment::query()
        ->where('event_id', $event->id)
        ->where('student_id', $student->id)
        ->where('presente', true)
        ->exists())->toBeTrue();
});

it('admin reaproveita aluno existente pelo e-mail', function () {
    $tenant = evpCreateTenant();
    $admin = evpMakeAdmin($tenant);
    $event = evpCreateEvent($tenant);
    $host = evpTenantHost($tenant);

    $alunoUser = User::create([
        'tenant_id' => $tenant->id,
        'name' => 'João Existente',
        'email' => 'joao-evp-'.fake()->unique()->safeEmail(),
        'password' => Hash::make('password'),
        'user_type' => User::TYPE_TENANT_USER,
        'status' => User::STATUS_ATIVO,
        'email_verified_at' => now(),
    ]);
    $alunoUser->assignRole('tenant_user');

    $student = Student::forceCreate([
        'tenant_id' => $tenant->id,
        'user_id' => $alunoUser->id,
        'email' => $alunoUser->email,
        'enrollment_number' => 'EV-'.fake()->unique()->numerify('######'),
        'cpf' => '52998224725',
        'cidade' => 'Araras',
        'status' => 'ativo',
    ]);

    $this->actingAs($admin)
        ->post('http://'.$host.'/admin/escola/eventos/'.$event->id.'/inscricoes', evpParticipantPayload([
            'name' => 'Outro Nome',
            'email' => $alunoUser->email,
            'cpf' => '52998224725',
            'presente' => '0',
        ]))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect(Student::query()->where('email', $alunoUser->email)->count())->toBe(1)
        ->and(EventEnrollment::query()->where('event_id', $event->id)->where('student_id', $student->id)->exists())->toBeTrue()
        ->and(User::query()->where('email', $alunoUser->email)->count())->toBe(1);
});

it('admin não duplica inscrição do mesmo participante no evento', function () {
    $tenant = evpCreateTenant();
    $admin = evpMakeAdmin($tenant);
    $event = evpCreateEvent($tenant);
    $host = evpTenantHost($tenant);
    $payload = evpParticipantPayload();

    $this->actingAs($admin)
        ->post('http://'.$host.'/admin/escola/eventos/'.$event->id.'/inscricoes', $payload)
        ->assertRedirect();

    $this->actingAs($admin)
        ->from('http://'.$host.'/admin/escola/eventos/'.$event->id.'/edit')
        ->post('http://'.$host.'/admin/escola/eventos/'.$event->id.'/inscricoes', $payload)
        ->assertRedirect()
        ->assertSessionHasErrors(['email'], errorBag: 'eventParticipant');

    expect(EventEnrollment::query()->where('event_id', $event->id)->count())->toBe(1);
});
