<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo conteúdo liberado</title>
</head>
<body style="margin:0;padding:24px;background:#eef3f9;font-family:Segoe UI,Arial,sans-serif;color:#0f172a;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:640px;margin:0 auto;background:#ffffff;border:1px solid #dbe5f1;border-radius:14px;overflow:hidden;">
        <tr>
            <td style="padding:28px 32px;background:linear-gradient(135deg,#4c1d95 0%,#1e1b4b 100%);color:#ffffff;">
                <div style="font-size:14px;opacity:.85;letter-spacing:.08em;text-transform:uppercase;">{{ config('app.name', 'Legiscola') }}</div>
                <div style="font-size:24px;font-weight:700;line-height:1.3;margin-top:10px;">Novo conteúdo liberado</div>
                <div style="font-size:14px;line-height:1.5;opacity:.9;margin-top:8px;">A direção regional disponibilizou um item do catálogo para a sua câmara.</div>
            </td>
        </tr>
        <tr>
            <td style="padding:30px 32px;">
                <p style="margin:0 0 14px 0;font-size:16px;line-height:1.6;">Olá, {{ $recipientName }}.</p>
                <p style="margin:0 0 18px 0;font-size:15px;line-height:1.7;color:#334155;">
                    {{ $license->director?->name ?? 'A direção regional' }} liberou um novo conteúdo para
                    {{ $license->tenant?->display_name ?? 'a sua câmara' }}. Você já pode
                    {{ $item?->isPalestra() ? 'agendar a palestra' : 'abrir a turma' }} pelo painel administrativo.
                </p>

                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f8fbff;border:1px solid #d6e5fb;border-radius:10px;padding:14px 16px;margin-bottom:18px;">
                    <tr>
                        <td style="padding:8px 0;font-size:14px;color:#1e3a5f;"><strong>Conteúdo:</strong> {{ $item?->titulo }}</td>
                    </tr>
                    <tr>
                        <td style="padding:8px 0;font-size:14px;color:#1e3a5f;"><strong>Tipo:</strong> {{ $item?->isPalestra() ? 'Palestra' : 'Curso' }}</td>
                    </tr>
                    @if ($license->professor_nome)
                        <tr>
                            <td style="padding:8px 0;font-size:14px;color:#1e3a5f;"><strong>Professor:</strong> {{ $license->professor_nome }}</td>
                        </tr>
                    @endif
                    @if ($license->exibir_ate)
                        <tr>
                            <td style="padding:8px 0;font-size:14px;color:#1e3a5f;"><strong>Disponível até:</strong> {{ $license->exibir_ate->format('d/m/Y') }}</td>
                        </tr>
                    @endif
                    @if ($license->palestra_em)
                        <tr>
                            <td style="padding:8px 0;font-size:14px;color:#1e3a5f;"><strong>Data combinada:</strong> {{ $license->palestra_em->format('d/m/Y H:i') }}</td>
                        </tr>
                    @endif
                </table>

                @if ($license->observacoes)
                    <div style="margin-top:4px;padding:16px 18px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;">
                        <p style="margin:0 0 10px 0;font-size:13px;color:#475569;text-transform:uppercase;letter-spacing:.04em;"><strong>Observações</strong></p>
                        <div style="margin:0;font-size:15px;line-height:1.75;color:#334155;">{!! nl2br(e($license->observacoes)) !!}</div>
                    </div>
                @endif

                <div style="margin:26px 0 8px;text-align:center;">
                    <a href="{{ $painelUrl }}"
                       style="display:inline-block;background:#4c1d95;color:#ffffff;text-decoration:none;font-weight:600;font-size:15px;padding:14px 28px;border-radius:10px;">
                        Ver conteúdo regional
                    </a>
                </div>
                <p style="margin:16px 0 0 0;font-size:13px;line-height:1.6;color:#64748b;text-align:center;">
                    Se o botão não funcionar, copie e cole no navegador:<br>
                    <span style="word-break:break-all;color:#4c1d95;">{{ $painelUrl }}</span>
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
