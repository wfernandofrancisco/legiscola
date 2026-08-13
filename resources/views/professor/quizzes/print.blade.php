<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Quiz - {{ $quiz->title }}</title>
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

        .questions-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        .questions-table th {
            background: #0f172a;
            color: #ffffff;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .4px;
            padding: 8px 6px;
            border: 1px solid #1e293b;
            text-align: left;
        }

        .questions-table td {
            border: 1px solid #dbe3ef;
            padding: 8px 7px;
            font-size: 11px;
            vertical-align: top;
        }

        .col-number {
            width: 36px;
            text-align: center;
        }

        .answers-list {
            margin: 0;
            padding: 0 0 0 14px;
        }

        .answers-list li {
            margin: 0 0 4px;
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
                    <h1 class="title">Quiz - {{ $quiz->title }}</h1>
                    <p class="subtitle">{{ $tenant?->display_name ?? $tenant?->name ?? 'Instituição' }}</p>
                    <p class="subtitle">Aplicação impressa para avaliação presencial</p>
                </td>
            </tr>
        </table>
    </div>

    <div class="meta-box">
        <table class="meta-grid">
            <tr>
                <td>
                    <p class="meta-row"><span class="meta-label">Quiz:</span> {{ $quiz->title }}</p>
                    <p class="meta-row"><span class="meta-label">Turmas:</span> {{ $quiz->courseClasses->pluck('name')->join(', ') ?: '—' }}</p>
                    <p class="meta-row"><span class="meta-label">Total de questões:</span> {{ $quiz->questions->count() }}</p>
                </td>
                <td>
                    <p class="meta-row"><span class="meta-label">Nota mínima:</span> {{ number_format((float) $quiz->min_score_to_pass, 2, ',', '.') }}%</p>
                    <p class="meta-row"><span class="meta-label">Impresso por:</span> {{ $printedBy ?? 'Usuário não identificado' }}</p>
                    <p class="meta-row"><span class="meta-label">Data/Hora impressão:</span> {{ ($printedAt ?? now())->format('d/m/Y H:i') }}</p>
                </td>
            </tr>
        </table>
    </div>

    <table class="questions-table">
        <thead>
            <tr>
                <th class="col-number">#</th>
                <th>Pergunta e alternativas</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($quiz->questions as $index => $question)
                <tr>
                    <td class="col-number">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $question->question }}</strong>
                        <ol class="answers-list" type="a">
                            @foreach ($question->answers as $answer)
                                <li>( &nbsp;&nbsp; ) {{ $answer->answer }}</li>
                            @endforeach
                        </ol>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="signature-box">
        <div class="signature-line">Assinatura do aplicador/responsável</div>
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
