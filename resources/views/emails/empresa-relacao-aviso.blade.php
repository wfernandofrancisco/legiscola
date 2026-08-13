<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aviso de relação de empresa</title>
</head>
<body style="margin:0;padding:24px;background:#eef3f9;font-family:Segoe UI,Arial,sans-serif;color:#0f172a;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:640px;margin:0 auto;background:#ffffff;border:1px solid #dbe5f1;border-radius:14px;overflow:hidden;">
        <tr>
            <td style="padding:28px 32px;background:linear-gradient(135deg,#0d4ea6 0%,#0a234a 100%);color:#ffffff;">
                <div style="font-size:14px;opacity:.85;letter-spacing:.08em;text-transform:uppercase;">{{ config('app.name') }}</div>
                <div style="font-size:24px;font-weight:700;line-height:1.3;margin-top:10px;">Aviso de relação de empresa</div>
                <div style="font-size:14px;line-height:1.5;opacity:.9;margin-top:8px;">Notificação automática para acompanhamento da relação.</div>
            </td>
        </tr>
        <tr>
            <td style="padding:30px 32px;">
                <p style="margin:0 0 14px 0;font-size:16px;line-height:1.6;">Olá,</p>
                <p style="margin:0 0 18px 0;font-size:15px;line-height:1.7;color:#334155;">
                    Você recebeu um aviso sobre uma relação cadastrada no módulo de empresas.
                </p>

                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f8fbff;border:1px solid #d6e5fb;border-radius:10px;padding:14px 16px;">
                    <tr>
                        <td style="padding:8px 0;font-size:14px;color:#1e3a5f;"><strong>Título:</strong> {{ $relacao->titulo }}</td>
                    </tr>
                    <tr>
                        <td style="padding:8px 0;font-size:14px;color:#1e3a5f;"><strong>Empresa:</strong> {{ $relacao->empresa?->razao_social ?? $relacao->empresa?->nome_fantasia ?? 'N/I' }}</td>
                    </tr>
                    <tr>
                        <td style="padding:8px 0;font-size:14px;color:#1e3a5f;"><strong>Status:</strong> {{ $relacao->status_label }}</td>
                    </tr>
                    <tr>
                        <td style="padding:8px 0;font-size:14px;color:#1e3a5f;"><strong>Prioridade:</strong> {{ $relacao->prioridade_label }}</td>
                    </tr>
                    @if($relacao->data_relacao)
                        <tr>
                            <td style="padding:8px 0;font-size:14px;color:#1e3a5f;"><strong>Data da relação:</strong> {{ $relacao->data_relacao->format('d/m/Y') }}</td>
                        </tr>
                    @endif
                </table>

                @if($relacao->descricao)
                    <div style="margin-top:16px;padding:14px 16px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;">
                        <p style="margin:0 0 8px 0;font-size:13px;color:#475569;"><strong>Descrição</strong></p>
                        <p style="margin:0;font-size:14px;line-height:1.7;color:#334155;">{{ $relacao->descricao }}</p>
                    </div>
                @endif

                <div style="margin:26px 0 20px;text-align:center;">
                    <a href="{{ rtrim(config('app.url'), '/') . '/admin/empresas/' . $relacao->empresa_id . '#relacoes' }}"
                       style="display:inline-block;background:#0d4ea6;color:#ffffff;text-decoration:none;font-weight:600;font-size:15px;padding:14px 28px;border-radius:10px;">
                        Abrir no painel
                    </a>
                </div>
            </td>
        </tr>
        <tr>
            <td style="padding:20px 32px;background:#f8fafc;border-top:1px solid #e2e8f0;font-size:12px;line-height:1.6;color:#64748b;">
                {{ config('app.name') }}<br>
                Este e-mail foi enviado automaticamente com base na data de aviso da relação.
            </td>
        </tr>
    </table>
</body>
</html>
