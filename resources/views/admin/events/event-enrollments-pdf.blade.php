<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Lista de inscritos — {{ $event->title }}</title>
    <style>
        @page {
            margin: 26px 28px 30px 28px;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            color: #1e293b;
            font-size: 11.5px;
        }

        .header {
            border: 1px solid #dbe3ef;
            border-left: 5px solid #0f172a;
            border-radius: 10px;
            padding: 14px;
            margin-bottom: 14px;
            background: #f8fafc;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: middle;
        }

        .logo-cell {
            width: 120px;
        }

        .logo {
            max-height: 68px;
            max-width: 110px;
        }

        .title {
            font-size: 18px;
            font-weight: 700;
            margin: 0;
            color: #0f172a;
        }

        .subtitle {
            margin: 3px 0 0;
            color: #475569;
            font-size: 11.5px;
        }

        .meta-box {
            margin-bottom: 12px;
            border: 1px solid #dbe3ef;
            border-radius: 10px;
            padding: 10px 12px 7px;
            background: #ffffff;
        }

        .meta-row {
            margin: 0 0 5px;
        }

        .meta-label {
            color: #334155;
            font-weight: 700;
        }

        .meta-grid {
            width: 100%;
            border-collapse: collapse;
        }

        .meta-grid td {
            width: 50%;
            vertical-align: top;
            padding-right: 12px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        .data-table th {
            background: #0f172a;
            color: #ffffff;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .35px;
            padding: 8px 5px;
            border: 1px solid #1e293b;
        }

        .data-table td {
            border: 1px solid #dbe3ef;
            padding: 6px 5px;
            font-size: 10px;
            vertical-align: top;
            word-wrap: break-word;
        }

        .col-num {
            width: 28px;
            text-align: center;
        }

        .col-name {
            width: 26%;
        }

        .col-email {
            width: 32%;
        }

        .col-cpf {
            width: 18%;
            text-align: center;
        }

        .col-birth {
            width: 14%;
            text-align: center;
        }

        .footer {
            position: fixed;
            bottom: 8px;
            left: 28px;
            right: 28px;
            border-top: 1px solid #cbd5e1;
            padding-top: 6px;
            font-size: 10px;
            color: #64748b;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }

        .footer-left {
            text-align: left;
        }

        .footer-right {
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="header">
        <table class="header-table">
            <tr>
                <td class="logo-cell">
                    @if(!empty($logoPath))
                        <img src="{{ $logoPath }}" class="logo" alt="Logo">
                    @endif
                </td>
                <td>
                    <h1 class="title">Lista de inscritos</h1>
                    <p class="subtitle">{{ $tenant?->display_name ?? $tenant?->name ?? 'Instituição' }}</p>
                    <p class="subtitle">Relatório de inscrições no evento</p>
                </td>
            </tr>
        </table>
    </div>

    <div class="meta-box">
        <table class="meta-grid">
            <tr>
                <td>
                    <p class="meta-row"><span class="meta-label">Evento:</span> {{ $event->title }}</p>
                    <p class="meta-row"><span class="meta-label">Data do evento:</span> {{ $event->date_time?->format('d/m/Y H:i') ?? '—' }}</p>
                    <p class="meta-row"><span class="meta-label">Local:</span> {{ $event->city ? trim($event->city.($event->state ? ' / '.$event->state : '')) : '—' }}</p>
                </td>
                <td>
                    <p class="meta-row"><span class="meta-label">Impresso por:</span> {{ $printedBy ?? 'Usuário não identificado' }}</p>
                    <p class="meta-row"><span class="meta-label">Data/Hora impressão:</span> {{ ($printedAt ?? now())->format('d/m/Y H:i') }}</p>
                    <p class="meta-row"><span class="meta-label">Total de inscritos:</span> {{ $enrollments->count() }}</p>
                </td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th class="col-num">#</th>
                <th class="col-name">Nome</th>
                <th class="col-email">E-mail</th>
                <th class="col-cpf">CPF</th>
                <th class="col-birth">Nascimento</th>
            </tr>
        </thead>
        <tbody>
            @foreach($enrollments as $index => $row)
                @php
                    $stu = $row->student;
                    $u = $stu?->user;
                    $nome = $u?->name ?? $stu?->email ?? '—';
                    $email = $u?->email ?? $stu?->email ?? '—';
                    $cpfRaw = $stu?->cpf ?? $u?->cpf ?? '';
                    $cpfMasked = \App\Support\SensitiveMask::cpfHalfMasked($cpfRaw);
                    $nasc = $stu?->birth_date?->format('d/m/Y') ?? '—';
                @endphp
                <tr>
                    <td class="col-num">{{ $index + 1 }}</td>
                    <td class="col-name">{{ $nome }}</td>
                    <td class="col-email">{{ \Illuminate\Support\Str::limit($email, 48) }}</td>
                    <td class="col-cpf">{{ $cpfMasked }}</td>
                    <td class="col-birth">{{ $nasc }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <table class="footer-table">
            <tr>
                <td class="footer-left">
                    {{ $tenant?->display_name ?? $tenant?->name ?? 'Instituição' }} — CPF parcialmente oculto por privacidade.
                </td>
                <td class="footer-right">
                    Emitido em {{ ($printedAt ?? now())->format('d/m/Y H:i') }}
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
