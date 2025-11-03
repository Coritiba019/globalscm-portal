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
  }

  .page-head{display:flex;align-items:center;justify-content:space-between;gap:1rem;margin-bottom:.75rem}
  .subtle{color:var(--muted)}
  .context-pill{display:inline-flex;align-items:center;gap:.5rem;background:#fff;border:1px solid var(--card-border);color:var(--ink);padding:.4rem .65rem;border-radius:999px;font-weight:600}

  /* Ações rápidas / filtros */
  .quick-filters{display:flex;gap:.5rem;flex-wrap:wrap}
  .chip{border:1px solid var(--card-border);background:#fff;color:var(--ink);padding:.35rem .6rem;border-radius:999px;font-weight:600}
  .chip.active{border-color:#b6e3ff;box-shadow:0 0 0 3px rgba(14,165,233,.15)}
  .btn-ghost{background:#fff;border:1px solid var(--card-border);color:var(--ink)}
  .btn-ghost:hover{border-color:var(--primary);box-shadow:0 0 0 3px rgba(14,165,233,.15)}

  /* KPIs */
  .kpi{position:relative;border:1px solid var(--card-border);border-radius:16px;background:#fff;padding:16px;height:100%;box-shadow:0 8px 24px rgba(0,0,0,.05)}
  .kpi::before{content:"";position:absolute;inset:0 0 auto 0;height:4px;border-radius:16px 16px 0 0;background:linear-gradient(90deg,var(--primary),var(--primary-2))}
  .kpi .label{color:var(--muted);font-size:.9rem}
  .kpi .value{font-weight:800;font-size:1.6rem;letter-spacing:.01em;color:var(--ink)}
  .kpi .meta{color:var(--muted);font-size:.85rem}
  .kpi .delta{display:inline-flex;gap:.35rem;align-items:center;font-weight:700;font-size:.9rem;padding:.15rem .5rem;border-radius:999px;border:1px solid}
  .kpi .delta.up{color:#166534;border-color:#bbf7d0;background:#ecfdf5}
  .kpi .delta.down{color:#7f1d1d;border-color:#fecaca;background:#fef2f2}

  /* Cards genéricos */
  .card-plain{border:1px solid var(--card-border);border-radius:16px;background:#fff;box-shadow:0 10px 28px rgba(0,0,0,.05)}
  .card-plain .card-header{border-bottom:1px dashed var(--card-border);background:#fff}
  .card-plain .card-footer{border-top:1px dashed var(--card-border);background:#fff}

  /* Gráficos */
  #dailyChart{max-height:320px}
  #statusChart{max-height:320px}
  .chart-legend{display:flex;gap:.75rem;align-items:center;flex-wrap:wrap;font-size:.9rem}
  .legend-dot{width:.75rem;height:.75rem;border-radius:999px;display:inline-block}

  /* Tabela */
  .table thead th{border-top:0;color:var(--muted);font-weight:700;letter-spacing:.02em;background:#fff;position:sticky;top:0;z-index:1}
  .table td,.table th{vertical-align:middle}
  .mono{font-variant-numeric:tabular-nums;font-family:ui-monospace,SFMono-Regular,Menlo,monospace}
  .badge-soft{border:1px solid transparent;font-weight:700}
  .badge-soft.success{background:#ecfdf5;color:#166534;border-color:#bbf7d0}
  .badge-soft.error{background:#fef2f2;color:#7f1d1d;border-color:#fecaca}
  .badge-soft.neutral{background:#f4f4f5;color:#27272a;border-color:#e4e4e7}

  /* Barra de sucesso (PIX) */
  .progress{height:.65rem}
  .progress .progress-bar{font-size:.7rem}

  /* Grid responsivo para 2 gráficos lado a lado */
  .grid-2{display:grid;gap:1rem}
  @media (min-width: 992px){ .grid-2{grid-template-columns:1fr 1fr} }
</style>
@endpush

@section('content')
<div class="container-xl py-2">

  {{-- Cabeçalho --}}
  <div class="page-head">
    <div>
      <h1 class="h4 mb-1">Visão Geral</h1>
      <div class="subtle">
        Conta ativa
        <span class="context-pill"><i class="bi bi-credit-card-2-front"></i> #{{ $digital }}</span>
        <span class="ms-2">Agência <strong>{{ $account->agencyNumber }}</strong> · Conta <strong>{{ $account->accountNumber }}</strong></span>
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
      {{-- (Opcional) Exportação – ajuste a rota se necessário --}}
      <a href="{{ route('reports.daily-transactions', ['digital' => $digital]) }}" class="btn btn-ghost btn-sm">
        <i class="bi bi-download me-1"></i> Exportar
      </a>
    </div>
  </div>

  {{-- KPIs --}}
  <div class="row g-3 mb-3">
    <div class="col-12 col-sm-6 col-lg-3">
      <div class="kpi">
        <div class="label">Saldo atual</div>
        <div class="value">R$ {{ $kpis['balance'] }}</div>
        <div class="meta mt-1">Período selecionado</div>
      </div>
    </div>

    <div class="col-12 col-sm-6 col-lg-3">
      <div class="kpi">
        <div class="label">Transações</div>
        <div class="d-flex align-items-center gap-2">
          <div class="value mb-0">{{ number_format($kpis['total_tx'],0,',','.') }}</div>
          @php $delta = $kpis['delta_tx'] ?? 0; @endphp
          @if($delta !== 0)
            <span class="delta {{ $delta>0 ? 'up' : 'down' }}">
              <i class="bi {{ $delta>0 ? 'bi-arrow-up-right' : 'bi-arrow-down-right' }}"></i>
              {{ ($delta>0?'+':'') . $delta }}%
            </span>
          @endif
        </div>
        <div class="meta mt-1">vs período anterior</div>
      </div>
    </div>

    <div class="col-12 col-sm-6 col-lg-3">
      <div class="kpi">
        <div class="label">PIX (sucesso)</div>
        @php
          $bd = $kpis['status_breakdown'] ?? [];
          $succ = (int)($bd['SUCCESS'] ?? ($kpis['pix_success'] ?? 0));
          $total = (int)($kpis['total_tx'] ?? 0);
          $rate = $total>0 ? round($succ*100/$total) : 0;
        @endphp
        <div class="value">{{ number_format($kpis['pix_success'] ?? $succ,0,',','.') }}</div>
        <div class="meta mt-2">Taxa de sucesso</div>
        <div class="progress" role="progressbar" aria-valuenow="{{ $rate }}" aria-valuemin="0" aria-valuemax="100">
          <div class="progress-bar bg-success" style="width: {{ $rate }}%">{{ $rate }}%</div>
        </div>
      </div>
    </div>

    <div class="col-12 col-sm-6 col-lg-3">
      <div class="kpi">
        <div class="label">Dias no período</div>
        <div class="value">{{ number_format($kpis['total_days'],0,',','.') }}</div>
        <div class="meta mt-1">{{ \Illuminate\Support\Str::of($period['initial'])->replace('-','/') }} — {{ \Illuminate\Support\Str::of($period['final'])->replace('-','/') }}</div>
      </div>
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
        <canvas id="dailyChart" height="110"></canvas>
      </div>
    </div>

    <div class="card-plain">
      <div class="card-header d-flex align-items-center justify-content-between">
        <div class="fw-semibold"><i class="bi bi-pie-chart me-2 text-primary"></i>Distribuição por status</div>
      </div>
      <div class="card-body d-flex align-items-center justify-content-center">
        <div class="w-100" style="max-width:520px">
          <canvas id="statusChart" height="110"></canvas>
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
        {{-- Slots para filtros rápidos, se quiser --}}
      </div>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive" style="max-height: 60vh">
        <table class="table table-hover table-nowrap align-middle mb-0">
          <thead>
            <tr>
              <th style="width: 180px;">Data/Hora</th>
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
  const bd    = @json($bdJS);
  const ok    = Number({{ $okJS }});
  const err   = Number({{ $errJS }});
  const pend  = Number({{ $pendJS }});

  // Helpers
  const $ = (sel,ctx=document)=>ctx.querySelector(sel);
  const br = n => (n ?? 0).toLocaleString('pt-BR');

  // Linha (daily)
  const ctx = document.getElementById('dailyChart').getContext('2d');
  const grad = ctx.createLinearGradient(0, 0, 0, 260);
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
      plugins:{
        legend: { display: false },
        tooltip: {
          mode: 'index',
          intersect: false,
          callbacks:{
            label: (ctx) => ` ${ctx.dataset.label}: ${br(ctx.parsed.y)}`
          }
        }
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

  // Donut (status)
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
      cutout: '62%',
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: (ctx) => ` ${ctx.label}: ${br(ctx.parsed)}`
          }
        }
      }
    }
  });
})();
</script>
@endsection
