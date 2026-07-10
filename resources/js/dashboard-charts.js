import {
    Chart,
    LineController,
    LineElement,
    PointElement,
    LinearScale,
    CategoryScale,
    BarController,
    BarElement,
    DoughnutController,
    ArcElement,
    Tooltip,
    Legend,
    Filler,
} from 'chart.js';

Chart.register(
    LineController,
    LineElement,
    PointElement,
    LinearScale,
    CategoryScale,
    BarController,
    BarElement,
    DoughnutController,
    ArcElement,
    Tooltip,
    Legend,
    Filler,
);

function cssVar(name, fallback) {
    const value = getComputedStyle(document.documentElement).getPropertyValue(name).trim();

    return value || fallback;
}

const palette = () => ({
    primary: cssVar('--color-primary', '#1976d2'),
    primaryMuted: cssVar('--color-primary-muted', '#e3f2fd'),
    success: cssVar('--color-success', '#2e7d32'),
    successMuted: cssVar('--color-success-muted', '#e8f5e9'),
    warning: cssVar('--color-warning', '#ed6c02'),
    warningMuted: cssVar('--color-warning-muted', '#fff3e0'),
    muted: cssVar('--color-text-muted', '#5f6368'),
    border: cssVar('--color-border', '#e0e0e0'),
});

const baseOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            labels: {
                boxWidth: 10,
                boxHeight: 10,
                color: palette().muted,
                font: { size: 11 },
            },
        },
    },
};

function initProfitTrend(canvas, data) {
    const colors = palette();

    return new Chart(canvas, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [{
                label: canvas.dataset.label || 'Profit',
                data: data.values,
                borderColor: colors.primary,
                backgroundColor: colors.primaryMuted,
                fill: true,
                tension: 0.35,
                pointRadius: 3,
                pointHoverRadius: 4,
                borderWidth: 2,
            }],
        },
        options: {
            ...baseOptions,
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { color: colors.muted, font: { size: 10 } },
                },
                y: {
                    beginAtZero: true,
                    grid: { color: colors.border },
                    ticks: { color: colors.muted, font: { size: 10 } },
                },
            },
            plugins: {
                ...baseOptions.plugins,
                legend: { display: false },
            },
        },
    });
}

function initCapitalVsProfit(canvas, data) {
    const colors = palette();
    const capital = Math.max(0, Number(data.capital) || 0);
    const profit = Math.max(0, Number(data.profit) || 0);
    const empty = capital === 0 && profit === 0;

    return new Chart(canvas, {
        type: 'doughnut',
        data: {
            labels: [canvas.dataset.capitalLabel || 'Capital', canvas.dataset.profitLabel || 'Profit'],
            datasets: [{
                data: empty ? [1, 0] : [capital, profit],
                backgroundColor: empty
                    ? [colors.border, colors.border]
                    : [colors.primaryMuted, colors.successMuted],
                borderColor: empty
                    ? [colors.muted, colors.muted]
                    : [colors.primary, colors.success],
                borderWidth: 1,
            }],
        },
        options: {
            ...baseOptions,
            cutout: '62%',
            plugins: {
                ...baseOptions.plugins,
                legend: { position: 'bottom' },
            },
        },
    });
}

function initInvestmentStatus(canvas, data) {
    const colors = palette();

    return new Chart(canvas, {
        type: 'bar',
        data: {
            labels: [
                canvas.dataset.activeLabel || 'Active',
                canvas.dataset.draftLabel || 'Draft',
                canvas.dataset.closedLabel || 'Closed',
            ],
            datasets: [{
                data: [data.active, data.draft, data.closed],
                backgroundColor: [colors.successMuted, colors.warningMuted, colors.primaryMuted],
                borderColor: [colors.success, colors.warning, colors.primary],
                borderWidth: 1,
                borderRadius: 6,
            }],
        },
        options: {
            ...baseOptions,
            plugins: {
                ...baseOptions.plugins,
                legend: { display: false },
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { color: colors.muted, font: { size: 10 } },
                },
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1, color: colors.muted, font: { size: 10 } },
                    grid: { color: colors.border },
                },
            },
        },
    });
}

function initMonthlyPayments(canvas, data) {
    const colors = palette();
    const paid = Math.max(0, Number(data.paid) || 0);
    const unpaid = Math.max(0, Number(data.unpaid) || 0);
    const empty = paid === 0 && unpaid === 0;

    return new Chart(canvas, {
        type: 'doughnut',
        data: {
            labels: [canvas.dataset.paidLabel || 'Paid', canvas.dataset.unpaidLabel || 'Unpaid'],
            datasets: [{
                data: empty ? [0, 1] : [paid, unpaid],
                backgroundColor: empty
                    ? [colors.border, colors.border]
                    : [colors.successMuted, colors.warningMuted],
                borderColor: empty
                    ? [colors.muted, colors.muted]
                    : [colors.success, colors.warning],
                borderWidth: 1,
            }],
        },
        options: {
            ...baseOptions,
            cutout: '62%',
            plugins: {
                ...baseOptions.plugins,
                legend: { position: 'bottom' },
            },
        },
    });
}

function initDashboardCharts() {
    const root = document.getElementById('dashboard-charts-root');
    if (! root) {
        return;
    }

    const dataEl = document.getElementById('dashboard-charts-data');
    const raw = dataEl?.textContent?.trim() ?? root.dataset.charts ?? '';

    if (! raw) {
        return;
    }

    let charts;
    try {
        charts = JSON.parse(raw);
    } catch (error) {
        console.error('Dashboard charts: invalid JSON payload', error);

        return;
    }

    root.querySelectorAll('[data-chart-type="profit-trend"]').forEach((canvas) => {
        if (charts.profitTrend) {
            initProfitTrend(canvas, charts.profitTrend);
        }
    });

    root.querySelectorAll('[data-chart-type="capital-vs-profit"]').forEach((canvas) => {
        if (charts.capitalVsProfit) {
            initCapitalVsProfit(canvas, charts.capitalVsProfit);
        }
    });

    root.querySelectorAll('[data-chart-type="investment-status"]').forEach((canvas) => {
        if (charts.investmentStatus) {
            initInvestmentStatus(canvas, charts.investmentStatus);
        }
    });

    root.querySelectorAll('[data-chart-type="monthly-payments"]').forEach((canvas) => {
        if (charts.monthlyPayments) {
            initMonthlyPayments(canvas, charts.monthlyPayments);
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initDashboardCharts);
} else {
    initDashboardCharts();
}
