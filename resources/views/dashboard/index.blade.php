@extends('layouts.app')

@section('title', 'Dashboard')

@push('head')
  {{-- (Opcional) Bootstrap Icons, caso seu layout ainda não inclua --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
@endpush

@push('styles')
<style>
  :root{
    --card-border:#e9ecef;
    --ink:#0f172a;
    --muted:#6c757d;
    --primary:#0ea5e9;
    --primary-2:#3abff8;
    --success:#16a34a;
    --danger:#ef4444;
    --warning:#f59e0b;

    /* Tipografia fluida */
    --fz-xxs: clamp(.78rem, 2vw, .85rem);
    --fz-xs:  clamp(.86rem, 2.2vw, .92rem);
    --fz-sm:  clamp(.92rem, 2.4vw, 1rem);
    --fz-md:  clamp(1.05rem, 2.7vw, 1.15rem);
    --fz-lg:  clamp(1.25rem, 3.2vw, 1.5rem);
  }

  /* Cabeçalho */
  .page-head{
    display:flex; align-items:center; justify-content:space-between; gap:1rem; margin-bottom:.75rem;
    flex-wrap:wrap;
  }
  .page-head h1{ font-size: var(--fz-md); margin: 0; }
  .subtle{ color:var(--muted); font-size: var(--fz-xs); }
  .context-wrap{ display:flex; align-items:center; gap:.5rem; flex-wrap:wrap; }
  .context-pill{
    display:inline-flex; align-items:center; gap:.5rem; background:#fff;
    border:1px solid var(--card-border); color:var(--ink); padding:.35rem .6rem; border-radius:999px; font-weight:650;
    font-size: var(--fz-xxs);
  }
  .context-line{ white-space:nowrap; }

  /* Ações / filtros */
  .quick-filters{ display:flex; gap:.5rem; flex-wrap:wrap }
  .chip{ border:1px solid var(--card-border); background:#fff; color:var(--ink); padding:.3rem .55rem; border-radius:999px; font-weight:650; font-size: var(--fz-xxs) }
  .chip.active{ border-color:#b6e3ff; box-shadow:0 0 0 3px rgba(14,165,233,.15) }
  .btn-ghost{ background:#fff; border:1px solid var(--card-border); color:var(--ink) }
  .btn-ghost:hover{ border-color:var(--primary); box-shadow: 0 0 0 3px rgba(14,165,233,.15) }

  /* KPIs */
  .kpi{
    position:relative; border:1px solid var(--card-border); border-radius:16px; background:#fff; padding:14px; height:100%;
    box-shadow:0 8px 24px rgba(0,0,0,.05); display:grid; gap:.4rem;
  }
  .kpi::before{
    content:""; position:absolute; inset:0 0 auto 0; height:4px; border-radius:16px 16px 0 0;
    background:linear-gradient(90deg,var(--primary),var(--primary-2));
  }
  .kpi .row1{ display:flex; align-items:center; justify-content:space-between; gap:.5rem; }
  .kpi .label{ color:var(--muted); font-size: var(--fz-xxs); display:flex; align-items:center; gap:.4rem; }
  .kpi .label i{ opacity:.8; }
  .kpi .value{ font-weight:850; font-size: var(--fz-lg); letter-spacing:.01em; color:var(--ink) }
  .kpi .meta{ color:var(--muted); font-size: var(--fz-xxs) }
  .kpi .delta{ display:inline-flex; gap:.35rem; align-items:center; font-weight:750; font-size: var(--fz-xxs); padding:.15rem .45rem; border-radius:999px; border:1px solid }
  .kpi .delta.up   { color:#166534; border-color:#bbf7d0; background:#ecfdf5 }
  .kpi .delta.down { color:#7f1d1d; border-color:#fecaca; background:#fef2f2 }

  /* Grid de KPIs: 2 col no mobile, 4 no desktop */
  .kpi-grid{ display:grid; gap:.8rem }
  @media (min-width: 540px){ .kpi-grid{ grid-template-columns: repeat(2, 1fr) } }
  @media (min-width: 992px){ .kpi-grid{ grid-template-columns: repeat(4, 1fr) } }

  /* Cards genéricos */
  .card-plain{ border:1px solid var(--card-border); border-radius:16px; background:#fff; box-shadow:0 10px 28px rgba(0,0,0,.05) }
  .card-plain .card-header{ border-bottom:1px dashed var(--card-border); background:#fff; padding:.8rem 1rem; font-size: var(--fz-sm) }
  .card-plain .card-body{ padding: 1rem }
  .card-plain .card-footer{ border-top:1px dashed var(--card-border); background:#fff; padding:.7rem 1rem; }

  /* Gráficos */
  #dailyChartWrap, #statusChartWrap{ height: clamp(240px, 42vw, 340px); }
  .chart-legend{ display:flex; gap:.6rem; align-items:center; flex-wrap:wrap; font-size: var(--fz-xxs) }
  .legend-dot{ width:.7rem; height:.7rem; border-radius:999px; display:inline-block }

  /* Grid responsivo para 2 gráficos lado a lado */
  .grid-2{ display:grid; gap:1rem }
  @media (min-width: 992px){ .grid-2{ grid-template-columns:1fr 1fr } }

  /* Tabela */
  .table thead th{ border-top:0; color:var(--muted); font-weight:750; letter-spacing:.02em; background:#fff; position:sticky; top:0; z-index:1; font-size: var(--fz-xxs) }
  .table td, .table th{ vertical-align:middle; }
  .table td{ font-size: var(--fz-xs) }
  .mono{ font-variant-numeric:tabular-nums; font-family:ui-monospace,SFMono-Regular,Menlo,monospace }
  .badge-soft{ border:1px solid transparent; font-weight:750; font-size: var(--fz-xxs) }
  .badge-soft.success{ background:#ecfdf5; color:#166534; border-color:#bbf7d0 }
  .badge-soft.error  { background:#fef2f2; color:#7f1d1d; border-color:#fecaca }
  .badge-soft.neutral{ background:#f4f4f5; color:#27272a; border-color:#e4e4e7 }
  .table-responsive{ max-height: 60vh; }

  /* Barra de sucesso (PIX) */
  .progress{ height:.6rem }
  .progress .progress-bar{ font-size:.65rem }

  /* Ajustes mobile */
  @media (max-width: 576px){
    .page-head{ gap:.6rem }
    .context-wrap{ gap:.35rem }
    .context-line{ width:100% }
  }
</style>
@endpush

@section('content')
<div class="container-xl py-2">

  {{-- Cabeçalho --}}
  <div class="page-head">
    <div>
      <h1 class="h4 mb-1">Visão Geral</h1>
      <div class="subtle">
        <span class="context-wrap">
          <span class="context-pill"><i class="bi bi-credit-card-2-front"></i> #{{ $digital }}</span>
          <span class="context-line">Agência <strong>{{ $account->agencyNumber }}</strong></span>
          <span class="context-line">· Conta <strong>{{ $account->accountNumber }}</strong></span>
        </span>
      </div>
    </div>
    <div class="quick-filters">
      <a href="{{ route('select-account') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left-circle me-1"></i> Trocar conta
      </a>
      <div class="dropdown">
        <button class="btn btn-ghost btn-sm dropdown-toggle" data-bs-toggle="dropdown">
          <i class="bi bi-calendar3 me-1"></i>
          {{ \Illuminate\Support\Str::of($period['initial'])->replace('-','/') }} – {{ \Illuminate\Support\Str::of($period['final'])->replace('-','/') }}
        </button>
        <div class="dropdown-menu dropdown-menu-end p-2" style="min-width: 260px;">
          <div class="d-flex gap-1 mb-2">
            <a class="chip {{ request('range')==='7d' ? 'active' : '' }}" href="?range=7d">7d</a>
            <a class="chip {{ request('range')==='15d' ? 'active' : '' }}" href="?range=15d">15d</a>
            <a class="chip {{ request('range')==='30d' ? 'active' : '' }}" href="?range=30d">30d</a>
          </div>
          <div class="dropdown-divider"></div>
          <form method="GET" class="px-1">
            <div class="row g-2">
              <div class="col-6">
                <input type="date" class="form-control" name="initialDate" value="{{ $period['initial'] }}">
              </div>
              <div class="col-6">
                <input type="date" class="form-control" name="finalDate" value="{{ $period['final'] }}">
              </div>
            </div>
            <div class="d-grid mt-2">
              <button class="btn btn-primary btn-sm"><i class="bi bi-funnel me-1"></i> Aplicar</button>
            </div>
          </form>
        </div>
      </div>
      <a href="{{ route('reports.daily-transactions', ['digital' => $digital]) }}" class="btn btn-ghost btn-sm">
        <i class="bi bi-download me-1"></i> Exportar
      </a>
    </div>
  </div>

  {{-- KPIs --}}
  <div class="kpi-grid mb-3">
    <div class="kpi">
      <div class="row1">
        <div class="label"><i class="bi bi-wallet2"></i> Saldo atual</div>
      </div>
      <div class="value">R$ {{ $kpis['balance'] }}</div>
      <div class="meta">Período selecionado</div>
    </div>

    <div class="kpi">
      <div class="row1">
        <div class="label"><i class="bi bi-repeat"></i> Transações</div>
        @php $delta = $kpis['delta_tx'] ?? 0; @endphp
        @if($delta !== 0)
          <span class="delta {{ $delta>0 ? 'up' : 'down' }}">
            <i class="bi {{ $delta>0 ? 'bi-arrow-up-right' : 'bi-arrow-down-right' }}"></i>
            {{ ($delta>0?'+':'') . $delta }}%
          </span>
        @endif
      </div>
      <div class="value mb-0">{{ number_format($kpis['total_tx'],0,',','.') }}</div>
      <div class="meta">vs período anterior</div>
    </div>

    <div class="kpi">
      <div class="row1">
        <div class="label"><i class="bi bi-lightning-charge"></i> PIX (sucesso)</div>
      </div>
      @php
        $bd = $kpis['status_breakdown'] ?? [];
        $succ = (int)($bd['SUCCESS'] ?? ($kpis['pix_success'] ?? 0));
        $total = (int)($kpis['total_tx'] ?? 0);
        $rate = $total>0 ? round($succ*100/$total) : 0;
      @endphp
      <div class="value">{{ number_format($kpis['pix_success'] ?? $succ,0,',','.') }}</div>
      <div class="meta mt-1">Taxa de sucesso</div>
      <div class="progress" role="progressbar" aria-valuenow="{{ $rate }}" aria-valuemin="0" aria-valuemax="100">
        <div class="progress-bar bg-success" style="width: {{ $rate }}%">{{ $rate }}%</div>
      </div>
    </div>

    <div class="kpi">
      <div class="row1">
        <div class="label"><i class="bi bi-calendar2-week"></i> Dias no período</div>
      </div>
      <div class="value">{{ number_format($kpis['total_days'],0,',','.') }}</div>
      <div class="meta">{{ \Illuminate\Support\Str::of($period['initial'])->replace('-','/') }} — {{ \Illuminate\Support\Str::of($period['final'])->replace('-','/') }}</div>
    </div>
  </div>

  {{-- Gráficos: linha + donut --}}
  <div class="grid-2 mb-3">
    <div class="card-plain">
      <div class="card-header d-flex align-items-center justify-content-between">
        <div class="fw-semibold"><i class="bi bi-graph-up-arrow me-2 text-primary"></i>Transações por dia</div>
        <div class="chart-legend">
          <span><span class="legend-dot" style="background: var(--primary)"></span> Total</span>
        </div>
      </div>
      <div class="card-body">
        <div id="dailyChartWrap"><canvas id="dailyChart"></canvas></div>
      </div>
    </div>

    <div class="card-plain">
      <div class="card-header d-flex align-items-center justify-content-between">
        <div class="fw-semibold"><i class="bi bi-pie-chart me-2 text-primary"></i>Distribuição por status</div>
      </div>
      <div class="card-body d-flex align-items-center justify-content-center">
        <div class="w-100" style="max-width:520px">
          <div id="statusChartWrap"><canvas id="statusChart"></canvas></div>
        </div>
      </div>
      @php
        $ok = (int)($bd['SUCCESS'] ?? ($kpis['pix_success'] ?? 0));
        $err = (int)($bd['ERROR'] ?? 0);
        $pend = (int)($bd['PENDING'] ?? 0);
      @endphp
      <div class="card-footer d-flex gap-3 flex-wrap small">
        <span><span class="legend-dot" style="background:#22c55e"></span> SUCCESS: <strong>{{ number_format($ok,0,',','.') }}</strong></span>
        <span><span class="legend-dot" style="background:#ef4444"></span> ERROR: <strong>{{ number_format($err,0,',','.') }}</strong></span>
        <span><span class="legend-dot" style="background:#f59e0b"></span> PENDING: <strong>{{ number_format($pend,0,',','.') }}</strong></span>
      </div>
    </div>
  </div>

  {{-- Últimas transações --}}
  <div class="card-plain">
    <div class="card-header d-flex justify-content-between align-items-center">
      <span class="fw-semibold"><i class="bi bi-clock-history me-2 text-primary"></i>Últimas transações</span>
      <div class="d-flex gap-2">
        {{-- Espaço para filtros rápidos se desejar --}}
      </div>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover table-nowrap align-middle mb-0">
          <thead>
            <tr>
              <th style="width: 160px;">Data/Hora</th>
              <th style="width: 90px;">Tipo</th>
              <th style="width: 120px;">Subtipo</th>
              <th>Descrição</th>
              <th class="text-end" style="width: 140px;">Valor</th>
              <th style="width: 120px;">Status</th>
            </tr>
          </thead>
          <tbody>
          @forelse ($tx as $t)
            @php
              $dt = \Illuminate\Support\Carbon::parse($t['dtHrTransaction'])->timezone('America/Sao_Paulo');
              $amt= (float) $t['amount'];
              $isC = ($t['type'] ?? 'C') === 'C';
              $st  = $t['status'] ?? '—';
              $badgeClass = $st==='SUCCESS' ? 'success' : ($st==='ERROR' ? 'error' : 'neutral');
            @endphp
            <tr>
              <td class="mono">{{ $dt->format('d/m/Y H:i:s') }}</td>
              <td>
                @if($isC)
                  <span class="badge bg-success-subtle text-success fw-semibold"><i class="bi bi-arrow-down-circle me-1"></i>Crédito</span>
                @else
                  <span class="badge bg-danger-subtle text-danger fw-semibold"><i class="bi bi-arrow-up-circle me-1"></i>Débito</span>
                @endif
              </td>
              <td><span class="text-uppercase">{{ $t['subType'] ?? '—' }}</span></td>
              <td class="text-truncate" style="max-width:520px;" title="{{ $t['description'] ?? '' }}">{{ $t['description'] ?? '—' }}</td>
              <td class="text-end mono {{ $isC ? 'text-success' : 'text-danger' }}">
                {{ $isC ? '+' : '-' }} R$ {{ number_format($amt, 2, ',', '.') }}
              </td>
              <td><span class="badge badge-soft {{ $badgeClass }}">{{ $st }}</span></td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="text-center py-4 text-muted">Sem transações no período.</td>
            </tr>
          @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js" crossorigin="anonymous"></script>

@php
  // Pré-cálculo seguro para o script
  $bdJS   = $kpis['status_breakdown'] ?? [];
  $okJS   = (int)($bdJS['SUCCESS'] ?? ($kpis['pix_success'] ?? 0));
  $errJS  = (int)($bdJS['ERROR']   ?? 0);
  $pendJS = (int)($bdJS['PENDING'] ?? 0);
@endphp

<script>
(function(){
  const labels = @json($labels);
  const values = @json($values);
  const ok    = Number({{ $okJS }});
  const err   = Number({{ $errJS }});
  const pend  = Number({{ $pendJS }});

  const br = n => (n ?? 0).toLocaleString('pt-BR');

  // LINE
  {
    const wrap = document.getElementById('dailyChartWrap');
    const ctx = document.getElementById('dailyChart').getContext('2d');
    const grad = ctx.createLinearGradient(0, 0, 0, wrap.clientHeight);
    grad.addColorStop(0, 'rgba(14,165,233,0.25)');
    grad.addColorStop(1, 'rgba(14,165,233,0.02)');

    new Chart(ctx, {
      type: 'line',
      data: {
        labels,
        datasets: [{
          label: 'Transações/dia',
          data: values,
          borderWidth: 2,
          borderColor: getComputedStyle(document.documentElement).getPropertyValue('--primary').trim() || '#0ea5e9',
          backgroundColor: grad,
          fill: true,
          tension: .25,
          pointRadius: 2,
          pointHoverRadius: 4
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins:{
          legend: { display: false },
          tooltip: { callbacks:{ label: (ctx) => ` ${ctx.dataset.label}: ${br(ctx.parsed.y)}` } }
        },
        scales: {
          x: { grid: { display:false } },
          y: {
            beginAtZero: true,
            ticks: { precision:0, callback: v => br(v) },
            grid: { color: 'rgba(0,0,0,.06)' }
          }
        }
      }
    });
  }

  // DONUT
  {
    const ctx2 = document.getElementById('statusChart').getContext('2d');
    new Chart(ctx2, {
      type: 'doughnut',
      data: {
        labels: ['SUCCESS', 'ERROR', 'PENDING'],
        datasets: [{
          data: [ok, err, pend],
          backgroundColor: ['#22c55e', '#ef4444', '#f59e0b'],
          borderWidth: 0
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '62%',
        plugins: {
          legend: { display: false },
          tooltip: { callbacks: { label: (ctx) => ` ${ctx.label}: ${br(ctx.parsed)}` } }
        }
      }
    });
  }
})();
</script>
@endsection
