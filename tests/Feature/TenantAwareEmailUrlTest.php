<?php

use App\Mail\PasswordResetMail;
use App\Mail\VerifyEmailMail;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    $this->tenant = Tenant::create([
        'name' => 'Cliente Dominio',
        'slug' => 'cliente-dominio',
        'domain' => 'cliente1.site.com.br',
        'razao_social' => 'Cliente Dominio LTDA',
        'cnpj' => '12345678000199',
        'status' => Tenant::STATUS_ATIVO,
        'cadastro_status' => Tenant::CADASTRO_ATIVO,
    ]);

    $this->user = User::create([
        'tenant_id' => $this->tenant->id,
        'name' => 'Usuario Tenant',
        'email' => 'usuario-tenant@test.local',
        'password' => Hash::make('password'),
        'user_type' => User::TYPE_TENANT_USER,
        'status' => User::STATUS_ATIVO,
        'email_verified_at' => null,
    ]);

    config()->set('app.url', 'https://site.com.br');
    config()->set('app.domain', 'site.com.br');
});

test('password reset usa dominio do tenant no link', function () {
    Mail::fake();

    $this->user->sendPasswordResetNotification('token-reset-123');

    Mail::assertQueued(PasswordResetMail::class, function (PasswordResetMail $mail) {
        return str_contains($mail->resetUrl, 'https://cliente1.site.com.br/')
            && str_contains($mail->resetUrl, 'token-reset-123')
            && str_contains($mail->resetUrl, urlencode($this->user->email));
    });
});

test('email verification usa dominio do tenant no link', function () {
    Mail::fake();

    $this->user->sendEmailVerificationNotification();

    Mail::assertQueued(VerifyEmailMail::class, function (VerifyEmailMail $mail) {
        return str_starts_with($mail->verificationUrl, 'https://cliente1.site.com.br/')
            && str_contains($mail->verificationUrl, '/tenant/verify-email/');
    });
});
