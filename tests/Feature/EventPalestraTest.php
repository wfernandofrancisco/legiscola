<?php

use App\Models\Event;
use App\Models\EventEnrollment;
use App\Models\EventEnrollmentPalestra;
use App\Models\EventPalestra;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
use App\Services\EnrollmentService;
use App\Support\TenantContext;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->withoutMiddleware(PreventRequestForgery::class);
});

function evp2Tenant(): Tenant
{
    return Tenant::create([
        'name' => 'Câmara Palestra',
        'slug' => 'evp2-'.uniqid(),
        'domain' => fake()->unique()->domainName(),
        'status' => Tenant::STATUS_ATIVO,
        'cadastro_status' => Tenant::CADASTRO_ATIVO,
        'estado' => 'SP',
    ]);
}

function evp2Admin(Tenant $tenant): User
{
    $user = User::create([
        'tenant_id' => $tenant->id,
        'name' => 'Admin Palestra',
        'email' => 'admin-palestra-'.fake()->unique()->safeEmail(),
        'password' => Hash::make('password'),
        'user_type' => User::TYPE_TENANT_ADMIN,
        'status' => User::STATUS_ATIVO,
        'email_verified_at' => now(),
    ]);
    $user->assignRole('tenant_admin');

    return $user;
}

function evp2Student(Tenant $tenant): Student
{
    $user = User::create([
        'tenant_id' => $tenant->id,
        'name' => 'Aluno Evento',
        'email' => 'aluno-evp2-'.fake()->unique()->safeEmail(),
        'password' => Hash::make('password'),
        'user_type' => User::TYPE_TENANT_USER,
        'status' => User::STATUS_ATIVO,
        'email_verified_at' => now(),
    ]);
    $user->assignRole('tenant_user');

    return Student::forceCreate([
        'tenant_id' => $tenant->id,
        'user_id' => $user->id,
        'email' => $user->email,
        'enrollment_number' => 'EP-'.fake()->unique()->numerify('######'),
        'cpf' => '52998224725',
        'cidade' => 'Araras',
        'status' => 'ativo',
    ]);
}

it('admin cria evento com duas palestras e sincroniza a data principal', function () {
    $tenant = evp2Tenant();
    $admin = evp2Admin($tenant);
    $host = $tenant->slug.'.'.config('app.domain');

    $this->actingAs($admin)
        ->post('http://'.$host.'/admin/escola/eventos', [
            'title' => 'Congresso legislativo',
            'allow_online_registration' => '1',
            'registration_starts_at' => now()->subHour()->format('Y-m-d\TH:i'),
            'registration_ends_at' => now()->addDays(10)->format('Y-m-d\TH:i'),
            'palestras' => [
                [
                    'title' => 'Abertura',
                    'date_time' => now()->addDays(2)->format('Y-m-d\TH:i'),
                    'com_certificado' => '1',
                    'palestrante_nome' => 'Ana Silva',
                    'palestrante_senha' => 'senha123',
                ],
                [
                    'title' => 'Encerramento',
                    'date_time' => now()->addDays(3)->format('Y-m-d\TH:i'),
                    'palestrante_nome' => 'Bruno Souza',
                    'palestrante_senha' => 'senha456',
                ],
            ],
        ])
        ->assertRedirect(route('admin.eventos.index'));

    $event = Event::query()->where('title', 'Congresso legislativo')->first();
    expect($event)->not->toBeNull()
        ->and($event->palestras)->toHaveCount(2)
        ->and($event->date_time->equalTo($event->palestras->first()->date_time))->toBeTrue();
});

it('inscrição de evento com palestras exige escolher ao menos uma', function () {
    $tenant = evp2Tenant();
    TenantContext::set($tenant->id);
    $event = Event::forceCreate([
        'tenant_id' => $tenant->id,
        'title' => 'Seminário 3 dias',
        'allow_online_registration' => true,
        'registration_starts_at' => now()->subDay(),
        'registration_ends_at' => now()->addDays(5),
        'date_time' => now()->addDays(2),
    ]);
    $a = EventPalestra::query()->create([
        'tenant_id' => $tenant->id,
        'event_id' => $event->id,
        'ordem' => 1,
        'title' => 'Dia 1',
        'date_time' => now()->addDays(2),
        'com_certificado' => true,
    ]);
    EventPalestra::query()->create([
        'tenant_id' => $tenant->id,
        'event_id' => $event->id,
        'ordem' => 2,
        'title' => 'Dia 2',
        'date_time' => now()->addDays(3),
        'com_certificado' => true,
    ]);
    $student = evp2Student($tenant);

    expect(fn () => app(EnrollmentService::class)->inscreverEmEvento((int) $student->id, (int) $event->id, []))
        ->toThrow(ValidationException::class);

    app(EnrollmentService::class)->inscreverEmEvento((int) $student->id, (int) $event->id, [$a->id]);

    $enrollment = EventEnrollment::query()->where('event_id', $event->id)->where('student_id', $student->id)->first();
    expect($enrollment)->not->toBeNull()
        ->and(EventEnrollmentPalestra::query()->where('event_enrollment_id', $enrollment->id)->count())->toBe(1)
        ->and(EventEnrollmentPalestra::query()->where('event_enrollment_id', $enrollment->id)->value('event_palestra_id'))->toBe($a->id);
});

it('evento de um dia continua inscrito sem escolher palestra', function () {
    $tenant = evp2Tenant();
    TenantContext::set($tenant->id);
    $event = Event::forceCreate([
        'tenant_id' => $tenant->id,
        'title' => 'Sessão única',
        'allow_online_registration' => true,
        'registration_starts_at' => now()->subDay(),
        'registration_ends_at' => now()->addDays(5),
        'date_time' => now()->addDays(2),
    ]);
    $student = evp2Student($tenant);

    app(EnrollmentService::class)->inscreverEmEvento((int) $student->id, (int) $event->id);

    expect(EventEnrollment::query()->where('event_id', $event->id)->where('student_id', $student->id)->exists())->toBeTrue()
        ->and(EventEnrollmentPalestra::query()->count())->toBe(0);
});
