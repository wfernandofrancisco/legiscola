<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aviso da turma</title>
</head>
<body style="margin:0;padding:24px;background:#eef3f9;font-family:Segoe UI,Arial,sans-serif;color:#0f172a;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:640px;margin:0 auto;background:#ffffff;border:1px solid #dbe5f1;border-radius:14px;overflow:hidden;">
        <tr>
            <td style="padding:28px 32px;background:linear-gradient(135deg,#0d4ea6 0%,#0a234a 100%);color:#ffffff;">
                <div style="font-size:14px;opacity:.85;letter-spacing:.08em;text-transform:uppercase;">{{ config('app.name', 'Legiscola') }}</div>
                <div style="font-size:24px;font-weight:700;line-height:1.3;margin-top:10px;">Aviso da turma</div>
                <div style="font-size:14px;line-height:1.5;opacity:.9;margin-top:8px;">Comunicado enviado pela secretaria escolar em nome da instituição.</div>
            </td>
        </tr>
        <tr>
            <td style="padding:30px 32px;">
                <p style="margin:0 0 14px 0;font-size:16px;line-height:1.6;">Olá, {{ $recipientName }}.</p>
                <p style="margin:0 0 18px 0;font-size:15px;line-height:1.7;color:#334155;">
                    Segue abaixo o conteúdo do aviso referente à sua matrícula. Em caso de dúvida, utilize os canais oficiais da escola ou da câmara.
                </p>

                @if($turma || $announcement->reference_date)
                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f8fbff;border:1px solid #d6e5fb;border-radius:10px;padding:14px 16px;margin-bottom:18px;">
                        @if($turma)
                            <tr>
                                <td style="padding:8px 0;font-size:14px;color:#1e3a5f;"><strong>Turma:</strong> {{ $turma->name }}</td>
                            </tr>
                            @if($turma->course)
                                <tr>
                                    <td style="padding:8px 0;font-size:14px;color:#1e3a5f;"><strong>Curso:</strong> {{ $turma->course->name }}</td>
                                </tr>
                            @endif
                        @endif
                        @if($announcement->reference_date)
                            <tr>
                                <td style="padding:8px 0;font-size:14px;color:#1e3a5f;"><strong>Data de referência:</strong> {{ \Illuminate\Support\Carbon::parse($announcement->reference_date)->format('d/m/Y') }}</td>
                            </tr>
                        @endif
                    </table>
                @endif

                <div style="margin-top:4px;padding:16px 18px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;">
                    <p style="margin:0 0 10px 0;font-size:13px;color:#475569;text-transform:uppercase;letter-spacing:.04em;"><strong>Mensagem</strong></p>
                    <div style="margin:0;font-size:15px;line-height:1.75;color:#334155;">{!! nl2br(e($announcement->body)) !!}</div>
                </div>

                <div style="margin:26px 0 8px;text-align:center;">
                    <a href="{{ rtrim(config('app.url'), '/') }}"
                       style="display:inline-block;background:#0d4ea6;color:#ffffff;text-decoration:none;font-weight:600;font-size:15px;padding:14px 28px;border-radius:10px;">
                        Acessar o sistema
                    </a>
                </div>
                <p style="margin:16px 0 0 0;font-size:13px;line-height:1.6;color:#64748b;text-align:center;">
                    Se o botão não funcionar, copie e cole no navegador:<br>
                    <span style="word-break:break-all;color:#0d4ea6;">{{ rtrim(config('app.url'), '/') }}</span>
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding:20px 32px;background:#f8fafc;border-top:1px solid #e2e8f0;font-size:12px;line-height:1.6;color:#64748b;">
                {{ config('app.name', 'Legiscola') }}<br>
                Este e-mail foi enviado automaticamente. Responda apenas aos contatos oficiais da instituição.
            </td>
        </tr>
    </table>
</body>
</html>
