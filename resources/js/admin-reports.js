import Chart from 'chart.js/auto';

const tickColor = '#64748b';
const gridColor = 'rgba(100, 116, 139, 0.18)';

const palette = ['#4f46e5', '#0ea5e9', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#14b8a6', '#6366f1', '#84cc16', '#f97316', '#64748b'];

function readPayload() {
    const el = document.getElementById('admin-reports-charts-data');
    if (!el || !el.textContent.trim()) {
        return null;
    }
    try {
        return JSON.parse(el.textContent);
    } catch {
        return null;
    }
}

function makeLine(canvas, payload) {
    if (!canvas || !payload?.line?.labels?.length) {
        return null;
    }
    return new Chart(canvas, {
        type: 'line',
        data: {
            labels: payload.line.labels,
            datasets: [
                {
                    label: 'Novas matrículas (por dia)',
                    data: payload.line.enrollments,
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79, 70, 229, 0.12)',
                    fill: true,
                    tension: 0.3,
                    pointRadius: 2,
                    pointHoverRadius: 4,
                },
                {
                    label: 'Novos cadastros de alunos (por dia)',
                    data: payload.line.students,
                    borderColor: '#0ea5e9',
                    backgroundColor: 'rgba(14, 165, 233, 0.12)',
                    fill: true,
                    tension: 0.3,
                    pointRadius: 2,
                    pointHoverRadius: 4,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            scales: {
                x: {
                    ticks: { color: tickColor, maxRotation: 0, autoSkip: true, maxTicksLimit: 14 },
                    grid: { color: gridColor },
                },
                y: {
                    beginAtZero: true,
                    ticks: { color: tickColor, precision: 0 },
                    grid: { color: gridColor },
                },
            },
            plugins: {
                legend: { position: 'top', labels: { color: tickColor, usePointStyle: true, boxWidth: 8 } },
            },
        },
    });
}

function makeDoughnut(canvas, dataset) {
    if (!canvas || !dataset?.labels?.length || !dataset?.data?.length) {
        return null;
    }
    const bg = dataset.labels.map((_, i) => palette[i % palette.length]);
    return new Chart(canvas, {
        type: 'doughnut',
        data: {
            labels: dataset.labels,
            datasets: [
                {
                    data: dataset.data,
                    backgroundColor: bg,
                    borderWidth: 0,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: { color: tickColor, boxWidth: 10, font: { size: 11 } },
                },
            },
        },
    });
}

function makeBarVertical(canvas, dataset, label) {
    if (!canvas || !dataset?.labels?.length) {
        return null;
    }
    return new Chart(canvas, {
        type: 'bar',
        data: {
            labels: dataset.labels,
            datasets: [
                {
                    label,
                    data: dataset.data,
                    backgroundColor: dataset.labels.map((_, i) => palette[i % palette.length]),
                    borderRadius: 6,
                    maxBarThickness: 28,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    ticks: { color: tickColor, autoSkip: false, maxRotation: 45, minRotation: 0, font: { size: 10 } },
                    grid: { display: false },
                },
                y: {
                    beginAtZero: true,
                    ticks: { color: tickColor, precision: 0 },
                    grid: { color: gridColor },
                },
            },
            plugins: {
                legend: { display: false },
            },
        },
    });
}

function makeBarHorizontal(canvas, dataset, label, xAxisTitle) {
    if (!canvas || !dataset?.labels?.length) {
        return null;
    }
    return new Chart(canvas, {
        type: 'bar',
        data: {
            labels: dataset.labels,
            datasets: [
                {
                    label,
                    data: dataset.data,
                    backgroundColor: dataset.labels.map((_, i) => palette[(i + 2) % palette.length]),
                    borderRadius: 4,
                    maxBarThickness: 18,
                },
            ],
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: { color: tickColor },
                    grid: { color: gridColor },
                    title: { display: Boolean(xAxisTitle), text: xAxisTitle, color: tickColor, font: { size: 11 } },
                },
                y: {
                    ticks: { color: tickColor, font: { size: 10 } },
                    grid: { display: false },
                },
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        title(items) {
                            const i = items[0]?.dataIndex;
                            return typeof i === 'number' ? String(dataset.labels[i] ?? '') : '';
                        },
                    },
                },
            },
        },
    });
}

function init() {
    const payload = readPayload();
    if (!payload) {
        return;
    }

    makeLine(document.getElementById('chart-report-line'), payload);
    makeDoughnut(document.getElementById('chart-class-status'), payload.classStatusDoughnut);
    makeDoughnut(document.getElementById('chart-sexo'), payload.sexoDoughnut);
    makeBarVertical(document.getElementById('chart-age'), payload.ageBar, 'Alunos novos no período');
    makeBarVertical(document.getElementById('chart-escolaridade'), payload.escolaridadeBar, 'Escolaridade (novos no período)');
    makeBarVertical(document.getElementById('chart-bairro'), payload.bairroBar, 'Bairro (novos no período)');
    makeBarVertical(document.getElementById('chart-enrollment-base'), payload.enrollmentBaseBar, 'Matrículas na base');
    makeBarVertical(document.getElementById('chart-enrollment-new'), payload.enrollmentNewBar, 'Novas matrículas no período');
    makeBarHorizontal(document.getElementById('chart-withdrawals'), payload.withdrawalsBar, 'Desistências', 'Quantidade');
    makeBarHorizontal(document.getElementById('chart-completion'), payload.completionBar, 'Taxa %', 'Percentual (%)');
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
