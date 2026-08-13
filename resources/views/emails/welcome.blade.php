<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bem-vindo</title>
</head>
<body style="margin:0;padding:24px;background:#eef3f9;font-family:Segoe UI,Arial,sans-serif;color:#0f172a;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:640px;margin:0 auto;background:#ffffff;border:1px solid #dbe5f1;border-radius:14px;overflow:hidden;">
        <tr>
            <td style="padding:28px 32px;background:linear-gradient(135deg,#0b3a7e 0%,#0a234a 100%);color:#ffffff;">
                <div style="font-size:14px;opacity:.85;letter-spacing:.08em;text-transform:uppercase;">Desenvolve City</div>
                <div style="font-size:24px;font-weight:700;line-height:1.3;margin-top:10px;">Seja bem-vindo</div>
                <div style="font-size:14px;line-height:1.5;opacity:.9;margin-top:8px;">Sua conta foi criada e seu ambiente esta pronto para uso.</div>
            </td>
        </tr>
        <tr>
            <td style="padding:30px 32px;">
                <p style="margin:0 0 14px 0;font-size:16px;line-height:1.6;">Ola, {{ $user->name }}.</p>
                <p style="margin:0 0 18px 0;font-size:15px;line-height:1.7;color:#334155;">
                    Sua conta foi criada com sucesso. Acesse seu painel e conclua as configuracoes iniciais.
                </p>
                <div style="margin:26px 0 20px;text-align:center;">
                    <a href="{{ $appUrl ?? config('app.url') }}" style="display:inline-block;background:#0b3a7e;color:#ffffff;text-decoration:none;font-weight:600;font-size:15px;padding:14px 28px;border-radius:10px;">Acessar meu painel</a>
                </div>
                <div style="background:#f8fbff;border:1px solid #d6e5fb;border-radius:10px;padding:14px 16px;font-size:13px;line-height:1.6;color:#1e3a5f;">
                    O link acima direciona para o dominio do tenant vinculado a sua conta.
                </div>
            </td>
        </tr>
        <tr>
            <td style="padding:20px 32px;background:#f8fafc;border-top:1px solid #e2e8f0;font-size:12px;line-height:1.6;color:#64748b;">
                Desenvolve City<br>
                Este e-mail foi enviado automaticamente para {{ $user->email }}.
            </td>
        </tr>
    </table>
</body>
</html>
