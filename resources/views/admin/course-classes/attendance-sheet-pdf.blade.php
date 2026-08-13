<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Ficha de Presença</title>
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

        .attendance-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        .attendance-table th {
            background: #0f172a;
            color: #ffffff;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .4px;
            padding: 8px 6px;
            border: 1px solid #1e293b;
        }

        .attendance-table td {
            border: 1px solid #dbe3ef;
            padding: 7px 6px;
            font-size: 11px;
        }

        .col-number {
            width: 36px;
            text-align: center;
        }

        .col-name {
            width: auto;
        }

        .col-status {
            width: 95px;
            text-align: center;
        }

        .col-presence {
            width: 78px;
            text-align: center;
            font-weight: 700;
        }

        .presence-blank {
            color: transparent;
            line-height: 18px;
        }

        .status-present {
            color: #166534;
        }

        .status-absent {
            color: #991b1b;
        }

        .signature-box {
            margin-top: 20px;
        }

        .signature-line {
            width: 280px;
            border-top: 1px solid #64748b;
            margin-top: 26px;
            padding-top: 5px;
            color: #475569;
            font-size: 10.5px;
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
                    @if($logoPath)
                        <img src="{{ $logoPath }}" class="logo" alt="Logo Prefeitura">
                    @endif
                </td>
                <td>
                    <h1 class="title">Ficha de Chamada</h1>
                    <p class="subtitle">{{ $tenant?->display_name ?? $tenant?->name ?? 'Instituição' }}</p>
                    <p class="subtitle">Relatório oficial de frequência</p>
                </td>
            </tr>
        </table>
    </div>

    <div class="meta-box">
        <table class="meta-grid">
            <tr>
                <td>
                    <p class="meta-row"><span class="meta-label">Turma:</span> {{ $turma->name }}</p>
                    <p class="meta-row"><span class="meta-label">Curso:</span> {{ $turma->course?->name ?? '—' }}</p>
                    <p class="meta-row"><span class="meta-label">Data da aula:</span> {{ \Illuminate\Support\Carbon::parse($date)->format('d/m/Y') }}</p>
                    @if (! empty($classLesson))
                        <p class="meta-row"><span class="meta-label">Aula:</span> {{ $classLesson->title }}</p>
                    @endif
                </td>
                <td>
                    <p class="meta-row"><span class="meta-label">Impresso por:</span> {{ $printedBy ?? 'Usuário não identificado' }}</p>
                    <p class="meta-row"><span class="meta-label">Data/Hora impressão:</span> {{ ($printedAt ?? now())->format('d/m/Y H:i') }}</p>
                    <p class="meta-row"><span class="meta-label">Total de alunos:</span> {{ $enrollments->count() }}</p>
                    <p class="meta-row"><span class="meta-label">Modo:</span> {{ ($printMode ?? 'filled') === 'blank' ? 'Ficha em branco (manual)' : 'Ficha preenchida' }}</p>
                </td>
            </tr>
        </table>
    </div>

    <table class="attendance-table">
        <thead>
            <tr>
                <th class="col-number">#</th>
                <th class="col-name">Aluno</th>
                <th class="col-status">Matrícula</th>
                <th class="col-presence">Presença</th>
            </tr>
        </thead>
        <tbody>
            @php
                $attendanceMap = $attendanceByStudent->toArray();
            @endphp
            @foreach($enrollments as $index => $enrollment)
                @php
                    $hasRecorded = array_key_exists($enrollment->student_id, $attendanceMap);
                    $isPresent = $hasRecorded ? (bool) $attendanceMap[$enrollment->student_id] : false;
                @endphp
                <tr>
                    <td class="col-number">{{ $index + 1 }}</td>
                    <td class="col-name">{{ $enrollment->student?->user?->name ?? $enrollment->student?->email ?? $enrollment->student?->user?->email ?? '—' }}</td>
                    <td class="col-status">{{ ucfirst($enrollment->status) }}</td>
                    @if(($printMode ?? 'filled') === 'blank')
                        <td class="col-presence presence-blank">_____</td>
                    @else
                        <td class="col-presence {{ $isPresent ? 'status-present' : 'status-absent' }}">
                            {{ $isPresent ? 'PRESENTE' : 'AUSENTE' }}
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="signature-box">
        <div class="signature-line">Assinatura do responsável pela chamada</div>
    </div>

    <div class="footer">
        <table class="footer-table">
            <tr>
                <td class="footer-left">
                    {{ $tenant?->display_name ?? $tenant?->name ?? 'Instituição' }}
                </td>
                <td class="footer-right">
                    Emitido em {{ ($printedAt ?? now())->format('d/m/Y H:i') }}
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
