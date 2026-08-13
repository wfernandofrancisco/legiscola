<?php

namespace Tests;

use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Mail;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Previne envio real de e-mails em todos os testes de feature
        Mail::fake();

        $this->seed(RolesAndPermissionsSeeder::class);

        // Em testes, gera uma URL fake para verificação de e-mail (a rota real
        // só é registrada nos environments que publicam as auth routes do Breeze).
        VerifyEmail::createUrlUsing(function ($notifiable) {
            return url("/api/v1/auth/email/verify/{$notifiable->getKey()}");
        });
    }
}
