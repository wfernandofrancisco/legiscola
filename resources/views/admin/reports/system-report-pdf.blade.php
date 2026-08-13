<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório do sistema</title>
    <style>
        @page {
            margin: 26px 28px 30px 28px;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            color: #1e293b;
            font-size: 10.5px;
        }

        .header {
            border: 1px solid #dbe3ef;
            border-left: 5px solid #0f172a;
            border-radius: 10px;
            padding: 12px;
            margin-bottom: 12px;
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
            width: 110px;
        }

        .logo {
            max-height: 60px;
            max-width: 100px;
        }

        .title {
            font-size: 16px;
            font-weight: 700;
            margin: 0;
            color: #0f172a;
        }

        .subtitle {
            margin: 2px 0 0;
            color: #475569;
            font-size: 10px;
        }

        .section {
            margin-bottom: 12px;
            page-break-inside: avoid;
        }

        .section-title {
            font-size: 11px;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 6px;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 4px;
        }

        .section-note {
            font-size: 9px;
            color: #64748b;
            margin: 0 0 6px;
        }

        .meta-box {
            margin-bottom: 10px;
            border: 1px solid #dbe3ef;
            border-radius: 8px;
            padding: 8px 10px;
            background: #ffffff;
        }

        .meta-label {
            color: #334155;
            font-weight: 700;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }

        .data-table th {
            background: #0f172a;
            color: #ffffff;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: .3px;
            padding: 6px 5px;
            border: 1px solid #1e293b;
        }

        .data-table td {
            border: 1px solid #dbe3ef;
            padding: 5px 5px;
            font-size: 9.5px;
        }

        .td-num {
            text-align: right;
        }

        .highlight {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            border-radius: 6px;
            padding: 6px 8px;
            margin-bottom: 8px;
            font-size: 9.5px;
            color: #065f46;
        }

        .footer {
            position: fixed;
            bottom: 6px;
            left: 28px;
            right: 28px;
            border-top: 1px solid #cbd5e1;
            padding-top: 5px;
            font-size: 9px;
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
                    @if (!empty($logoPath))
                        <img src="{{ $logoPath }}" class="logo" alt="Logo">
                    @endif
                </td>
                <td>
                    <h1 class="title">Relatório do sistema</h1>
                    <p class="subtitle">{{ $tenant?->display_name ?? $tenant?->name ?? 'Instituição' }}</p>
                    <p class="subtitle">Alunos, turmas e matrículas</p>
                </td>
            </tr>
        </table>
    </div>

    <div class="meta-box">
        <p style="margin:0 0 4px;"><span class="meta-label">Período:</span>
            {{ $periodStart->format('d/m/Y') }} — {{ $periodEnd->format('d/m/Y') }}</p>
        <p style="margin:0 0 4px;"><span class="meta-label">Emitido por:</span> {{ $printedBy ?? '—' }}</p>
        <p style="margin:0;"><span class="meta-label">Data/Hora:</span> {{ ($printedAt ?? now())->format('d/m/Y H:i') }}</p>
    </div>

    <div class="section">
        <h2 class="section-title">Resumo geral</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Indicador</th>
                    <th class="td-num">Valor</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Alunos cadastrados (base)</td>
                    <td class="td-num">{{ number_format($totalStudents, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Alunos com matrícula ativa (inscrito/cursando/concluído/baixa presença)</td>
                    <td class="td-num">{{ number_format($matriculatedDistinct, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Novos cadastros de alunos no período</td>
                    <td class="td-num">{{ number_format($studentsInPeriodCount, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Desistências no período (status + data atualização)</td>
                    <td class="td-num">{{ number_format($withdrawalsInPeriod, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    @if ($studentsInPeriodCount > 0)
        <div class="section">
            <h2 class="section-title">Perfil — alunos novos no período</h2>
            <p class="section-note">Idade referente à data de hoje; cadastros filtrados por data de criação no sistema.</p>
            <table class="data-table" style="margin-bottom:8px;">
                <thead>
                    <tr>
                        <th>Faixa etária</th>
                        <th class="td-num">Qtd</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($ageBuckets as $row)
                        <tr>
                            <td>{{ $row['label'] }}</td>
                            <td class="td-num">{{ number_format($row['total'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <table class="data-table" style="margin-bottom:8px;">
                <thead>
                    <tr>
                        <th>Sexo</th>
                        <th class="td-num">Qtd</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sexoCounts as $row)
                        <tr>
                            <td>{{ $row['label'] }}</td>
                            <td class="td-num">{{ number_format($row['total'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <table class="data-table" style="margin-bottom:8px;">
                <thead>
                    <tr>
                        <th>Escolaridade</th>
                        <th class="td-num">Qtd</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($escolaridadeCounts as $row)
                        <tr>
                            <td>{{ $row['label'] }}</td>
                            <td class="td-num">{{ number_format($row['total'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Bairro</th>
                        <th class="td-num">Qtd</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach (collect($bairroCounts)->take(20) as $row)
                        <tr>
                            <td>{{ $row['label'] }}</td>
                            <td class="td-num">{{ number_format($row['total'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div class="section">
        <h2 class="section-title">Turmas por situação</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Situação</th>
                    <th class="td-num">Na base</th>
                    <th class="td-num">Criadas no período</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($classStatusLabels as $key => $label)
                    <tr>
                        <td>{{ $label }}</td>
                        <td class="td-num">{{ number_format($classStatusCounts[$key] ?? 0, 0, ',', '.') }}</td>
                        <td class="td-num">{{ number_format($classStatusInPeriod[$key] ?? 0, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2 class="section-title">Matrículas — base e novas no período</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Status</th>
                    <th class="td-num">Base</th>
                    <th class="td-num">Novas no período</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($enrollmentStatusLabels as $key => $label)
                    <tr>
                        <td>{{ $label }}</td>
                        <td class="td-num">{{ number_format($enrollmentStatusAll[$key] ?? 0, 0, ',', '.') }}</td>
                        <td class="td-num">{{ number_format($newEnrollmentsByStatus[$key] ?? 0, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2 class="section-title">Desistências no período — por curso</h2>
        @if ($withdrawalsByCourse->isEmpty())
            <p class="section-note">Nenhum registro.</p>
        @else
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Curso</th>
                        <th class="td-num">Qtd</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($withdrawalsByCourse as $row)
                        <tr>
                            <td>{{ $row->course_name }}</td>
                            <td class="td-num">{{ number_format($row->total, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="section">
        <h2 class="section-title">Taxa de conclusão (turmas encerradas)</h2>
        <p class="section-note">Por curso: somatório de matrículas em turmas com status Concluído.</p>
        @if ($bestCompletion)
            <div class="highlight">
                <strong>Maior taxa:</strong> {{ $bestCompletion['course_name'] }}
                — {{ number_format($bestCompletion['rate'], 1, ',', '.') }}%
                ({{ number_format($bestCompletion['concluded'], 0, ',', '.') }} / {{ number_format($bestCompletion['total'], 0, ',', '.') }})
            </div>
        @endif
        @if ($completionByCourse->isEmpty())
            <p class="section-note">Sem dados.</p>
        @else
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Curso</th>
                        <th class="td-num">Concl.</th>
                        <th class="td-num">Total</th>
                        <th class="td-num">%</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($completionByCourse as $row)
                        <tr>
                            <td>{{ $row['course_name'] }}</td>
                            <td class="td-num">{{ number_format($row['concluded'], 0, ',', '.') }}</td>
                            <td class="td-num">{{ number_format($row['total'], 0, ',', '.') }}</td>
                            <td class="td-num">{{ number_format($row['rate'], 1, ',', '.') }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="footer">
        <table class="footer-table">
            <tr>
                <td class="footer-left">
                    {{ $tenant?->display_name ?? $tenant?->name ?? 'Instituição' }}
                </td>
                <td class="footer-right">
                    Relatório do sistema — {{ ($printedAt ?? now())->format('d/m/Y H:i') }}
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
