@extends('layouts.master')

@section('title', 'Dashboard - YPF Chat Station')

@section('breadcrumb')
<li class="breadcrumb-item active">Dashboard</li>
@endsection

@push('css')
<style>
    :root {
        --ypf-blue: #0033a0;
        --ypf-red: #e30613;
        --ypf-yellow: #ffd100;
    }

    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .filter-card {
        margin-bottom: 1.5rem;
    }

    .filter-row {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .filter-row .form-control {
        width: auto;
        min-width: 160px;
    }

    .preset-btns {
        display: flex;
        gap: 0.25rem;
    }

    .preset-btns .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.8125rem;
    }

    .kpi-card {
        text-align: center;
        padding: 1.25rem 0.75rem;
        border: 1px solid var(--cui-border-color);
        border-radius: 0.5rem;
        background: var(--cui-body-bg);
        height: 100%;
    }

    .kpi-card .kpi-icon {
        font-size: 1.5rem;
        margin-bottom: 0.5rem;
        color: var(--ypf-blue);
    }

    .kpi-card .kpi-value {
        font-size: 1.75rem;
        font-weight: 700;
        line-height: 1.2;
    }

    .kpi-card .kpi-label {
        font-size: 0.8125rem;
        color: var(--cui-secondary-color);
        margin-top: 0.25rem;
    }

    .section-title {
        font-size: 1.125rem;
        font-weight: 600;
        margin: 2rem 0 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid var(--ypf-blue);
    }

    .chart-card {
        border: 1px solid var(--cui-border-color);
        border-radius: 0.5rem;
        padding: 1rem;
        margin-bottom: 1rem;
        background: var(--cui-body-bg);
        height: 100%;
    }

    .chart-card h6 {
        font-weight: 600;
        margin-bottom: 0.75rem;
        color: var(--cui-body-color);
    }

    .chart-container {
        position: relative;
        width: 100%;
        max-height: 300px;
    }

    .chart-container canvas {
        max-height: 300px;
    }

    .model-table {
        font-size: 0.875rem;
    }

    .model-table th {
        font-weight: 600;
        white-space: nowrap;
    }

    .loading-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.15);
        z-index: 1050;
        align-items: center;
        justify-content: center;
    }

    .loading-overlay.active {
        display: flex;
    }

    .stat-inline {
        display: flex;
        align-items: center;
        gap: 1rem;
        justify-content: center;
        margin-top: 0.75rem;
    }

    .stat-inline .stat-item {
        text-align: center;
    }

    .stat-inline .stat-value {
        font-size: 1.25rem;
        font-weight: 600;
    }

    .stat-inline .stat-label {
        font-size: 0.75rem;
        color: var(--cui-secondary-color);
    }

    .btn-ypf {
        background-color: var(--ypf-blue);
        border-color: var(--ypf-blue);
        color: white;
    }

    .btn-ypf:hover {
        background-color: #002680;
        border-color: #002680;
        color: white;
    }
</style>
@endpush

@section('content')
<div class="dashboard-header">
    <h1 class="h3 mb-0"><i class="fas fa-chart-line me-2"></i>Dashboard</h1>
</div>

{{-- Date Filter --}}
<div class="card filter-card">
    <div class="card-body py-2">
        <div class="filter-row">
            <label class="form-label mb-0 fw-semibold">Desde</label>
            <input type="date" class="form-control form-control-sm" id="filterFrom">
            <label class="form-label mb-0 fw-semibold">Hasta</label>
            <input type="date" class="form-control form-control-sm" id="filterTo">
            <div class="preset-btns">
                <button class="btn btn-outline-secondary" onclick="setPreset(7)">7d</button>
                <button class="btn btn-outline-secondary" onclick="setPreset(30)">30d</button>
                <button class="btn btn-outline-secondary" onclick="setPreset(90)">90d</button>
                <button class="btn btn-outline-secondary" onclick="setPreset(0)">Todo</button>
            </div>
            <button class="btn btn-sm btn-ypf" onclick="loadDashboard()">
                <i class="fas fa-sync-alt me-1"></i>Aplicar
            </button>
            <span id="loadingSpinner" class="d-none">
                <i class="fas fa-spinner fa-spin text-primary"></i>
            </span>
        </div>
    </div>
</div>

{{-- KPI Cards --}}
<div class="row g-3 mb-3" id="kpiRow">
    <div class="col-6 col-md-4 col-xl">
        <div class="kpi-card">
            <div class="kpi-icon"><i class="fas fa-comments"></i></div>
            <div class="kpi-value" id="kpiTotalChats">-</div>
            <div class="kpi-label">Total Chats</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl">
        <div class="kpi-card">
            <div class="kpi-icon"><i class="fas fa-check-circle"></i></div>
            <div class="kpi-value" id="kpiFinished">-</div>
            <div class="kpi-label">Finalizados</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl">
        <div class="kpi-card">
            <div class="kpi-icon"><i class="fas fa-play-circle"></i></div>
            <div class="kpi-value" id="kpiActive">-</div>
            <div class="kpi-label">Activos</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl">
        <div class="kpi-card">
            <div class="kpi-icon"><i class="fas fa-dollar-sign"></i></div>
            <div class="kpi-value" id="kpiTotalCost">-</div>
            <div class="kpi-label">Costo Total (USD)</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl">
        <div class="kpi-card">
            <div class="kpi-icon"><i class="fas fa-dollar-sign"></i></div>
            <div class="kpi-value" id="kpiTotalCostArs">-</div>
            <div class="kpi-label">Costo Total (ARS)</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl">
        <div class="kpi-card">
            <div class="kpi-icon"><i class="fas fa-star"></i></div>
            <div class="kpi-value" id="kpiAvgScore">-</div>
            <div class="kpi-label">Score Promedio</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl">
        <div class="kpi-card">
            <div class="kpi-icon"><i class="fas fa-percentage"></i></div>
            <div class="kpi-value" id="kpiPassRate">-</div>
            <div class="kpi-label">Tasa Aprobacion</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl">
        <div class="kpi-card">
            <div class="kpi-icon"><i class="fas fa-microchip"></i></div>
            <div class="kpi-value" id="kpiTotalTokens">-</div>
            <div class="kpi-label">Total Tokens</div>
        </div>
    </div>
</div>

{{-- COSTS Section --}}
<h4 class="section-title"><i class="fas fa-dollar-sign me-2"></i>Costos</h4>
<div class="row g-3">
    <div class="col-md-4">
        <div class="chart-card">
            <h6>Costos por Servicio</h6>
            <div class="chart-container">
                <canvas id="chartCostsByService"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="chart-card">
            <h6>Costos por Proveedor</h6>
            <div class="chart-container">
                <canvas id="chartCostsByProvider"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="chart-card">
            <h6>Costos en el Tiempo</h6>
            <div class="chart-container">
                <canvas id="chartCostsTimeSeries"></canvas>
            </div>
        </div>
    </div>
</div>
<div class="row g-3 mt-1">
    <div class="col-12">
        <div class="chart-card">
            <h6>Costos por Modelo</h6>
            <div class="table-responsive">
                <table class="table table-sm model-table mb-0" id="modelTable">
                    <thead>
                        <tr>
                            <th>Proveedor</th>
                            <th>Modelo</th>
                            <th class="text-end">Mensajes</th>
                            <th class="text-end">Tokens</th>
                            <th class="text-end">Costo (USD)</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- USAGE Section --}}
<h4 class="section-title"><i class="fas fa-chart-bar me-2"></i>Uso</h4>
<div class="row g-3">
    <div class="col-md-4">
        <div class="chart-card">
            <h6>Chats por Dia</h6>
            <div class="chart-container">
                <canvas id="chartChatsPerDay"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="chart-card">
            <h6>Distribucion por Proveedor</h6>
            <div class="chart-container">
                <canvas id="chartProviderDist"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="chart-card">
            <h6>Tipos de Chat</h6>
            <div class="chart-container">
                <canvas id="chartChatTypes"></canvas>
            </div>
            <div class="stat-inline" id="usageStatsInline">
                <div class="stat-item">
                    <div class="stat-value" id="statAvgMsg">-</div>
                    <div class="stat-label">Msg / Chat (prom)</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- EVALUATIONS Section --}}
<h4 class="section-title"><i class="fas fa-clipboard-check me-2"></i>Evaluaciones</h4>
<div class="row g-3">
    <div class="col-md-4">
        <div class="chart-card">
            <h6>Tendencia de Score</h6>
            <div class="chart-container">
                <canvas id="chartScoreTrend"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="chart-card">
            <h6>Tasa Aprobacion / Desaprobacion</h6>
            <div class="chart-container">
                <canvas id="chartPassFail"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="chart-card">
            <h6>Evaluaciones por Agente</h6>
            <div class="chart-container">
                <canvas id="chartEvalsByAgent"></canvas>
            </div>
        </div>
    </div>
</div>
<div class="row g-3 mt-1">
    <div class="col-12">
        <div class="chart-card">
            <h6>Rendimiento por Criterio GEMA</h6>
            <div style="position: relative; width: 100%; height: 420px;">
                <canvas id="chartCriteriaPerf"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- AGENTS SUMMARY --}}
<h4 class="section-title"><i class="fas fa-robot me-2"></i>Resumen por Agente</h4>
<div class="card mb-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm mb-0" id="agentsSummaryTable">
                <thead>
                    <tr>
                        <th>Agente</th>
                        <th class="text-end">Chats</th>
                        <th class="text-end">Score Prom.</th>
                        <th class="text-end">Tasa Aprob.</th>
                        <th class="text-end">Costo Prom. (USD)</th>
                        <th class="text-end">Costo Total (USD)</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

{{-- Loading Overlay --}}
<div class="loading-overlay" id="loadingOverlay">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Cargando...</span>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
const charts = {};

const COLORS = {
    blue: '#0033a0',
    red: '#e30613',
    yellow: '#ffd100',
    teal: '#17a2b8',
    green: '#28a745',
    orange: '#fd7e14',
    purple: '#6f42c1',
    pink: '#e83e8c',
    cyan: '#20c997',
    indigo: '#6610f2',
    gray: '#6c757d',
    navy: '#001f5b',
    lime: '#84cc16',
};

const PALETTE = [COLORS.blue, COLORS.red, COLORS.yellow, COLORS.teal, COLORS.green, COLORS.orange, COLORS.purple, COLORS.pink, COLORS.cyan, COLORS.indigo, COLORS.gray, COLORS.navy, COLORS.lime];

function alpha(hex, a) {
    const r = parseInt(hex.slice(1, 3), 16);
    const g = parseInt(hex.slice(3, 5), 16);
    const b = parseInt(hex.slice(5, 7), 16);
    return `rgba(${r},${g},${b},${a})`;
}

function createChart(canvasId, config) {
    if (charts[canvasId]) {
        charts[canvasId].destroy();
    }
    const ctx = document.getElementById(canvasId);
    if (!ctx) return null;
    charts[canvasId] = new Chart(ctx, config);
    return charts[canvasId];
}

function formatNumber(n) {
    if (n === null || n === undefined) return '-';
    if (n >= 1000000) return (n / 1000000).toFixed(1) + 'M';
    if (n >= 1000) return (n / 1000).toFixed(1) + 'K';
    return n.toString();
}

function formatCost(n) {
    if (n === null || n === undefined) return '-';
    return '$' + parseFloat(n).toFixed(4);
}

// Date preset
function setPreset(days) {
    if (days === 0) {
        document.getElementById('filterFrom').value = '';
        document.getElementById('filterTo').value = '';
    } else {
        const to = new Date();
        const from = new Date();
        from.setDate(from.getDate() - days);
        document.getElementById('filterFrom').value = from.toISOString().split('T')[0];
        document.getElementById('filterTo').value = to.toISOString().split('T')[0];
    }
    loadDashboard();
}

async function loadDashboard() {
    const from = document.getElementById('filterFrom').value;
    const to = document.getElementById('filterTo').value;
    const spinner = document.getElementById('loadingSpinner');

    let url = '/api/dashboard';
    const params = [];
    if (from) params.push('from=' + from);
    if (to) params.push('to=' + to);
    if (params.length) url += '?' + params.join('&');

    spinner.classList.remove('d-none');

    try {
        const response = await apiFetch(url);
        const json = await response.json();
        if (json.status) {
            renderDashboard(json.data);
        } else {
            console.error('Dashboard error:', json.message);
        }
    } catch (error) {
        console.error('Error loading dashboard:', error);
    } finally {
        spinner.classList.add('d-none');
    }
}

function renderDashboard(data) {
    renderKpis(data.kpis);
    renderCostsByService(data.costs_by_service);
    renderCostsByProvider(data.costs_by_provider);
    renderCostsTimeSeries(data.costs_time_series);
    renderCostsPerModel(data.costs_per_model);
    renderChatsPerDay(data.chats_per_day);
    renderProviderDistribution(data.provider_distribution);
    renderUsageStats(data.usage_stats);
    renderScoreTrend(data.score_trend);
    renderPassFailRate(data.pass_fail_rate);
    renderCriteriaPerformance(data.criteria_performance);
    renderEvaluationsByAgent(data.evaluations_by_agent);
    renderAgentsSummary(data.agents_summary);
}

// KPIs
function renderKpis(kpis) {
    document.getElementById('kpiTotalChats').textContent = formatNumber(kpis.total_chats);
    document.getElementById('kpiFinished').textContent = formatNumber(kpis.finished);
    document.getElementById('kpiActive').textContent = formatNumber(kpis.active);
    document.getElementById('kpiTotalCost').textContent = formatCost(kpis.total_cost);
    document.getElementById('kpiTotalCostArs').textContent = '$' + (parseFloat(kpis.total_cost) * 2000).toFixed(2);
    document.getElementById('kpiAvgScore').textContent = kpis.avg_score ? kpis.avg_score.toFixed(1) : '0';
    document.getElementById('kpiPassRate').textContent = kpis.pass_rate + '%';
    document.getElementById('kpiTotalTokens').textContent = formatNumber(kpis.total_tokens);
}

// Costs by service - Doughnut
function renderCostsByService(costs) {
    const labels = ['LLM', 'TTS', 'STT', 'Imagen', 'Evaluacion'];
    const values = [costs.llm, costs.tts, costs.stt, costs.image, costs.evaluation];
    const colors = [COLORS.blue, COLORS.teal, COLORS.green, COLORS.yellow, COLORS.purple];

    createChart('chartCostsByService', {
        type: 'doughnut',
        data: {
            labels,
            datasets: [{
                data: values,
                backgroundColor: colors.map(c => alpha(c, 0.8)),
                borderColor: colors,
                borderWidth: 1,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, padding: 8, font: { size: 11 } } },
                tooltip: {
                    callbacks: { label: ctx => ctx.label + ': $' + ctx.parsed.toFixed(4) }
                }
            }
        }
    });
}

// Costs by provider - Horizontal bar
function renderCostsByProvider(providers) {
    const labels = providers.map(p => p.provider);
    const values = providers.map(p => p.cost);

    createChart('chartCostsByProvider', {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                data: values,
                backgroundColor: labels.map((_, i) => alpha(PALETTE[i % PALETTE.length], 0.7)),
                borderColor: labels.map((_, i) => PALETTE[i % PALETTE.length]),
                borderWidth: 1,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: { label: ctx => '$' + ctx.parsed.x.toFixed(4) }
                }
            },
            scales: {
                x: { ticks: { callback: v => '$' + v.toFixed(2) } }
            }
        }
    });
}

// Costs time series - Stacked line
function renderCostsTimeSeries(series) {
    const labels = series.map(s => s.date);
    const config = {
        type: 'line',
        data: {
            labels,
            datasets: [
                { label: 'LLM', data: series.map(s => s.llm), borderColor: COLORS.blue, backgroundColor: alpha(COLORS.blue, 0.1), fill: true, tension: 0.3 },
                { label: 'TTS', data: series.map(s => s.tts), borderColor: COLORS.teal, backgroundColor: alpha(COLORS.teal, 0.1), fill: true, tension: 0.3 },
                { label: 'STT', data: series.map(s => s.stt), borderColor: COLORS.green, backgroundColor: alpha(COLORS.green, 0.1), fill: true, tension: 0.3 },
                { label: 'Imagen', data: series.map(s => s.image), borderColor: COLORS.yellow, backgroundColor: alpha(COLORS.yellow, 0.1), fill: true, tension: 0.3 },
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, padding: 8, font: { size: 11 } } },
                tooltip: {
                    callbacks: { label: ctx => ctx.dataset.label + ': $' + ctx.parsed.y.toFixed(4) }
                }
            },
            scales: {
                x: { ticks: { maxTicksLimit: 10, font: { size: 10 } } },
                y: { stacked: true, ticks: { callback: v => '$' + v.toFixed(2) } }
            }
        }
    };
    createChart('chartCostsTimeSeries', config);
}

// Costs per model - Table
function renderCostsPerModel(models) {
    const tbody = document.querySelector('#modelTable tbody');
    if (!models.length) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3">Sin datos</td></tr>';
        return;
    }
    tbody.innerHTML = models.map(m => `
        <tr>
            <td>${escapeHtml(m.provider)}</td>
            <td><code>${escapeHtml(m.model)}</code></td>
            <td class="text-end">${formatNumber(m.messages_count)}</td>
            <td class="text-end">${formatNumber(m.total_tokens)}</td>
            <td class="text-end">${formatCost(m.total_cost)}</td>
        </tr>
    `).join('');
}

// Chats per day - Bar chart
function renderChatsPerDay(data) {
    createChart('chartChatsPerDay', {
        type: 'bar',
        data: {
            labels: data.map(d => d.date),
            datasets: [{
                label: 'Chats',
                data: data.map(d => d.count),
                backgroundColor: alpha(COLORS.blue, 0.7),
                borderColor: COLORS.blue,
                borderWidth: 1,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { ticks: { maxTicksLimit: 10, font: { size: 10 } } },
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });
}

// Provider distribution - Pie
function renderProviderDistribution(data) {
    createChart('chartProviderDist', {
        type: 'pie',
        data: {
            labels: data.map(d => d.provider),
            datasets: [{
                data: data.map(d => d.message_count),
                backgroundColor: data.map((_, i) => alpha(PALETTE[i % PALETTE.length], 0.7)),
                borderColor: data.map((_, i) => PALETTE[i % PALETTE.length]),
                borderWidth: 1,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, padding: 8, font: { size: 11 } } }
            }
        }
    });
}

// Usage stats - Doughnut + stat
function renderUsageStats(stats) {
    createChart('chartChatTypes', {
        type: 'doughnut',
        data: {
            labels: ['Simple', 'Avanzado'],
            datasets: [{
                data: [stats.simple_count, stats.advanced_count],
                backgroundColor: [alpha(COLORS.blue, 0.7), alpha(COLORS.red, 0.7)],
                borderColor: [COLORS.blue, COLORS.red],
                borderWidth: 1,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, padding: 8, font: { size: 11 } } }
            }
        }
    });

    document.getElementById('statAvgMsg').textContent = stats.avg_messages_per_chat;
}

// Score trend - Line
function renderScoreTrend(data) {
    createChart('chartScoreTrend', {
        type: 'line',
        data: {
            labels: data.map(d => d.date),
            datasets: [{
                label: 'Score Promedio',
                data: data.map(d => d.avg_score),
                borderColor: COLORS.blue,
                backgroundColor: alpha(COLORS.blue, 0.1),
                fill: true,
                tension: 0.3,
                pointRadius: 4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: { label: ctx => 'Score: ' + ctx.parsed.y.toFixed(1) + ' (' + data[ctx.dataIndex].count + ' evals)' }
                }
            },
            scales: {
                x: { ticks: { maxTicksLimit: 10, font: { size: 10 } } },
                y: { min: 0, max: 100 }
            }
        }
    });
}

// Pass/Fail - Doughnut
function renderPassFailRate(data) {
    createChart('chartPassFail', {
        type: 'doughnut',
        data: {
            labels: ['Aprobados', 'Desaprobados'],
            datasets: [{
                data: [data.passed, data.failed],
                backgroundColor: [alpha(COLORS.green, 0.7), alpha(COLORS.red, 0.7)],
                borderColor: [COLORS.green, COLORS.red],
                borderWidth: 1,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, padding: 8, font: { size: 11 } } }
            }
        }
    });
}

// Evaluations by agent - Bar
function renderEvaluationsByAgent(data) {
    createChart('chartEvalsByAgent', {
        type: 'bar',
        data: {
            labels: data.map(d => d.agent_name),
            datasets: [
                {
                    label: 'Score Promedio',
                    data: data.map(d => d.avg_score),
                    backgroundColor: alpha(COLORS.blue, 0.7),
                    borderColor: COLORS.blue,
                    borderWidth: 1,
                },
                {
                    label: 'Evaluaciones',
                    data: data.map(d => d.eval_count),
                    backgroundColor: alpha(COLORS.yellow, 0.7),
                    borderColor: COLORS.yellow,
                    borderWidth: 1,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, padding: 8, font: { size: 11 } } }
            },
            scales: {
                x: { ticks: { font: { size: 10 } } },
                y: { beginAtZero: true }
            }
        }
    });
}

// Criteria performance - Horizontal bar
function renderCriteriaPerformance(data) {
    createChart('chartCriteriaPerf', {
        type: 'bar',
        data: {
            labels: data.map(d => d.name),
            datasets: [{
                label: 'Score Promedio',
                data: data.map(d => d.avg_score),
                backgroundColor: data.map(d => d.avg_score >= 70 ? alpha(COLORS.green, 0.7) : alpha(COLORS.red, 0.7)),
                borderColor: data.map(d => d.avg_score >= 70 ? COLORS.green : COLORS.red),
                borderWidth: 1,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => {
                            const item = data[ctx.dataIndex];
                            return 'Score: ' + item.avg_score + ' | Aprob: ' + item.pass_rate + '%';
                        }
                    }
                }
            },
            scales: {
                x: { min: 0, max: 100, ticks: { callback: v => v + '%' } },
                y: { ticks: { font: { size: 11 } } }
            }
        }
    });
}

// Agents summary - Table
function renderAgentsSummary(agents) {
    const tbody = document.querySelector('#agentsSummaryTable tbody');
    if (!agents.length) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-3">Sin datos</td></tr>';
        return;
    }
    tbody.innerHTML = agents.map(a => `
        <tr>
            <td><i class="fas fa-robot me-1 text-primary"></i>${escapeHtml(a.name)}</td>
            <td class="text-end">${a.chats_count}</td>
            <td class="text-end">${a.avg_score ? a.avg_score.toFixed(1) : '-'}</td>
            <td class="text-end">${a.pass_rate}%</td>
            <td class="text-end">${formatCost(a.avg_cost)}</td>
            <td class="text-end">${formatCost(a.total_cost)}</td>
        </tr>
    `).join('');
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Init
document.addEventListener('DOMContentLoaded', function() {
    setPreset(30);
});
</script>
@endpush
