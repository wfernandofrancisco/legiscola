<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    $user = App\Models\User::first();
    if ($user) {
        $token = Illuminate\Support\Facades\Password::createToken($user);
        $resetUrl = 'https://example.com/reset-password?token=' . $token . '&email=' . urlencode($user->email);

        echo "Enviando email de teste para Mailtrap...\n";
        echo "Usuário: {$user->name} ({$user->email})\n";
        echo "Destinatário: wfernandofrancisco@gmail.com\n\n";

        Illuminate\Support\Facades\Mail::to('wfernandofrancisco@gmail.com')->send(new App\Mail\PasswordResetMail($user, $resetUrl));

        echo "✅ Email enviado com sucesso para Mailtrap!\n\n";
        echo "📧 Acesse https://mailtrap.io para visualizar o email\n";
        echo "📋 Procure pelo email com assunto '[Desenvolve City] Recuperação de Senha'\n";
        echo "🎨 Você poderá ver o layout completo do template\n";

    } else {
        echo '❌ Nenhum usuário encontrado.';
    }
} catch (Exception $e) {
    echo '❌ Erro: ' . $e->getMessage() . "\n";
}