<?php

use App\Models\Tenant;
use App\Models\User;
use App\Support\UniqueConstraintUserMessage;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->withoutMiddleware(PreventRequestForgery::class);
});

function trUniqueHost(Tenant $tenant): string
{
    return $tenant->slug.'.'.config('app.domain');
}

function trUniqueTenant(): Tenant
{
    return Tenant::create([
        'name' => 'Câmara Cadastro',
        'slug' => 'tr-reg-'.uniqid(),
        'domain' => fake()->unique()->domainName(),
        'status' => Tenant::STATUS_ATIVO,
        'cadastro_status' => Tenant::CADASTRO_ATIVO,
    ]);
}

function trRegisterPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'William Teste',
        'email' => 'novo-'.fake()->unique()->safeEmail(),
        'password' => 'Password1!',
        'password_confirmation' => 'Password1!',
        'cpf' => '52998224725',
        'birth_date' => '1990-05-20',
        'sexo' => 'masculino',
        'cidade' => 'Araras',
    ], $overrides);
}

it('converte violação de CPF único em mensagem amigável', function () {
    $previous = new PDOException("SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry '43202862863' for key 'users_cpf_unique'");
    $exception = new UniqueConstraintViolationException('mysql', 'insert into users', [], $previous);

    $mapped = UniqueConstraintUserMessage::fromException($exception);

    expect($mapped['field'])->toBe('cpf')
        ->and($mapped['message'])->toContain('CPF já está cadastrado');
});

it('converte violação de e-mail único em mensagem amigável', function () {
    $previous = new PDOException("SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'a@b.com' for key 'users_email_unique'");
    $exception = new UniqueConstraintViolationException('mysql', 'insert into users', [], $previous);

    $mapped = UniqueConstraintUserMessage::fromException($exception);

    expect($mapped['field'])->toBe('email')
        ->and($mapped['message'])->toContain('e-mail já está cadastrado');
});

it('cadastro informa que o CPF já existe em vez de 500', function () {
    $tenant = trUniqueTenant();
    $host = trUniqueHost($tenant);

    User::create([
        'tenant_id' => $tenant->id,
        'name' => 'Já Cadastrado',
        'email' => 'existente-'.fake()->unique()->safeEmail(),
        'password' => Hash::make('password'),
        'user_type' => User::TYPE_TENANT_USER,
        'status' => User::STATUS_ATIVO,
        'cpf' => '52998224725',
        'email_verified_at' => now(),
    ]);

    $this->from('http://'.$host.'/tenant/register')
        ->post('http://'.$host.'/tenant/register', trRegisterPayload([
            'email' => 'outro-'.fake()->unique()->safeEmail(),
            'cpf' => '52998224725',
        ]))
        ->assertRedirect()
        ->assertSessionHasErrors(['cpf']);

    expect(User::query()->where('cpf', '52998224725')->count())->toBe(1);
});

it('cadastro informa que o e-mail já existe', function () {
    $tenant = trUniqueTenant();
    $host = trUniqueHost($tenant);
    $email = 'email-'.fake()->unique()->safeEmail();

    User::create([
        'tenant_id' => $tenant->id,
        'name' => 'Já Cadastrado',
        'email' => $email,
        'password' => Hash::make('password'),
        'user_type' => User::TYPE_TENANT_USER,
        'status' => User::STATUS_ATIVO,
        'cpf' => '11144477735',
        'email_verified_at' => now(),
    ]);

    $this->from('http://'.$host.'/tenant/register')
        ->post('http://'.$host.'/tenant/register', trRegisterPayload([
            'email' => $email,
            'cpf' => '52998224725',
        ]))
        ->assertRedirect()
        ->assertSessionHasErrors(['email']);
});
