@extends('layouts.app')

@section('title', 'Relatório · Transações por Dia')

@push('styles')
<style>
  :root{
    --ink:#0f172a;
    --ink-weak:#334155;
    --muted:#6b7280;
    --surface:#ffffff;
    --surface-2:#f7f8fb;
    --border:#e5e7eb;
    --primary:#0ea5e9;
    --primary-2:#3abff8;
    --success:#16a34a;

    /* Tipografia fluida */
    --fz-xxs: clamp(.78rem, 2vw, .85rem);
    --fz-xs:  clamp(.86rem, 2.2vw, .95rem);
    --fz-sm:  clamp(.95rem, 2.4vw, 1.05rem);
    --fz-md:  clamp(1.1rem, 3vw, 1.25rem);
    --fz-lg:  clamp(1.25rem, 3.5vw, 1.6rem);
  }
  [data-theme="dark"]{
    --ink:#e7ebf0;
    --ink-weak:#c6d0da;
    --muted:#9aa3ad;
    --surface:#0f172a;
    --surface-2:#0b1220;
    --border:rgba(255,255,255,.12);
  }

  /* ——— Layout base ——— */
  .page-head{
    display:flex; align-items:center; justify-content:space-between; gap:1rem; margin-bottom:.75rem;
    flex-wrap:wrap;
  }
  .page-head h2{ font-size: var(--fz-md); margin:0; }
  .subtle{ color:var(--muted); font-size: var(--fz-xs); }
  .btn-ghost{ background:var(--surface); border:1px solid var(--border); color:var(--ink) }
  .btn-ghost:hover{ border-color:var(--primary); box-shadow:0 0 0 3px rgba(14,165,233,.18) }
  .context-pill{
    display:inline-flex; align-items:center; gap:.45rem; padding:.35rem .6rem; border-radius:999px;
    background:var(--surface); border:1px solid var(--border); font-weight:700; font-size: var(--fz-xxs);
  }

  /* ——— Cards KPI ——— */
  .kpi{ position:relative; border:1px solid var(--border); border-radius:16px; background:var(--surface); box-shadow:0 10px 28px rgba(0,0,0,.06); padding:14px; height:100% }
  .kpi::before{ content:""; position:absolute; inset:0 0 auto 0; height:4px; border-radius:16px 16px 0 0; background:linear-gradient(90deg,var(--primary),var(--primary-2)) }
  .kpi .label{ color:var(--muted); font-size: var(--fz-xxs) }
  .kpi .value{ font-weight:800; font-size: var(--fz-lg); color:var(--ink) }
  .kpi .meta{ color:var(--muted); font-size: var(--fz-xxs) }

  /* KPI grid: 2 col mobile → 4 col desktop */
  .kpi-grid{ display:grid; gap:.75rem }
  @media (min-width: 540px){ .kpi-grid{ grid-template-columns: repeat(2, 1fr) } }
  @media (min-width: 992px){ .kpi-grid{ grid-template-columns: repeat(4, 1fr) } }

  /* ——— Cartões genéricos ——— */
  .card-plain{ border:1px solid var(--border); border-radius:16px; background:var(--surface); box-shadow:0 10px 28px rgba(0,0,0,.06) }
  .card-plain .card-header{ border-bottom:1px dashed var(--border); background:var(--surface); padding:.8rem 1rem; font-weight:600 }
  .card-plain .card-footer{ border-top:1px dashed var(--border); background:var(--surface); }

  /* ——— Chips/presets ——— */
  .chip{ display:inline-flex; align-items:center; gap:.4rem; padding:.35rem .6rem; border:1px solid var(--border); border-radius:999px; background:var(--surface); color:var(--ink); font-weight:650; font-size: var(--fz-xxs) }
  .chip i{ opacity:.75 }
  .chip.active{ border-color:#b6e3ff; box-shadow:0 0 0 3px rgba(14,165,233,.15) }

  /* ——— Gráfico ——— */
  #dailyChartWrap{ height: clamp(240px, 40vw, 360px); }
  #dailyChart{ width:100%; height:100% }
  .chart-legend{ display:flex; gap:.6rem; align-items:center; flex-wrap:wrap; font-size: var(--fz-xxs) }
  .legend-dot{ width:.7rem; height:.7rem; border-radius:999px; display:inline-block }

  /* ——— Tabela ——— */
  .table thead th{ border-top:0; color:var(--ink-weak); font-weight:750; letter-spacing:.02em; background:var(--surface); position:sticky; top:0; z-index:1; font-size: var(--fz-xxs) }
  .table td{ font-size: var(--fz-xs) }
  .mono{ font-variant-numeric:tabular-nums; font-family:ui-monospace,SFMono-Regular,Menlo,monospace }
  .table-hover tbody tr:hover{ background:rgba(14,165,233,.06) }

  /* ——— Filtros responsivos ——— */
  .filters-grid{ display:grid; gap:.6rem }
  @media (min-width: 768px){ .filters-grid{ grid-template-columns: repeat(6, 1fr) } }
  .filters-actions{ display:flex; justify-content:flex-end; gap:.5rem; flex-wrap:wrap }

  /* ——— Print ——— */
  @media print{
    .page-head .btn, .card-header .btn, .card-header .chip, .card-header .dropdown, .filters-actions{ display:none !important }
    .card-plain, .kpi{ box-shadow:none !important }
    body{ background:#fff }
  }
</style>
@endpush

@section('content')
<div class="container py-4">

  {{-- Cabeçalho --}}
  <div class="page-head">
    <div>
      <h2 class="h4 mb-1">Relatório: Transações por Dia</h2>
      <div class="subtle">
        Contexto
        <span class="context-pill"><i class="bi bi-credit-card-2-front"></i> Digital <strong>{{ $digital ?: '—' }}</strong></span>
        @if(($initial ?? false) && ($final ?? false))
          <span class="context-pill"><i class="bi bi-calendar3"></i> {{ \Illuminate\Support\Str::of($initial)->replace('-','/') }} — {{ \Illuminate\Support\Str::of($final)->replace('-','/') }}</span>
        @endif
      </div>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-ghost btn-sm" onclick="window.print()"><i class="bi bi-printer me-1"></i> Imprimir</button>
      <a class="btn btn-primary btn-sm"
         href="{{ request()->fullUrlWithQuery(array_merge(request()->query(), ['format' => 'csv'])) }}">
        <i class="bi bi-download me-1"></i> Exportar CSV
      </a>
    </div>
  </div>

  {{-- Filtros --}}
  <div class="card-plain mb-3">
    <div class="card-header d-flex align-items-center justify-content-between">
      <div class="fw-semibold"><i class="bi bi-funnel me-2 text-primary"></i>Filtros</div>
      @if(!empty($filters))
        <div class="d-none d-md-flex gap-2 flex-wrap">
          @foreach($filters as $k => $v)
            @if($v !== null && $v !== '')
              <span class="chip active"><i class="bi bi-filter"></i> {{ $k }}: <strong>{{ is_array($v)?json_encode($v):$v }}</strong></span>
            @endif
          @endforeach
        </div>
      @endif
    </div>
    <div class="card-body">
      <form method="get" class="filters-grid">
        <div>
          <label class="form-label">Digital</label>
          <input type="text" name="digital" class="form-control" value="{{ old('digital', $digital) }}" placeholder="ex: 1222">
        </div>
        <div>
          <label class="form-label">Tipo</label>
          @php $type = $type ?? 'todos'; @endphp
          <select name="type" class="form-select">
            <option value="todos"  {{ $type==='todos'?'selected':'' }}>Todos</option>
            <option value="credit" {{ $type==='credit'?'selected':'' }}>Crédito</option>
            <option value="debit"  {{ $type==='debit'?'selected':'' }}>Débito</option>
          </select>
        </div>
        <div>
          <label class="form-label">SubTipo</label>
          <input type="text" name="subType" class="form-control" value="{{ old('subType', $subType) }}" placeholder="PIX, TED...">
        </div>
        <div>
          <label class="form-label">Status</label>
          <input type="text" name="status" class="form-control" value="{{ old('status', $status) }}" placeholder="SUCCESS, ERROR">
        </div>
        <div>
          <label class="form-label">Inicial</label>
          <input type="date" name="initial" class="form-control" value="{{ old('initial', $initial) }}">
        </div>
        <div>
          <label class="form-label">Final</label>
          <input type="date" name="final" class="form-control" value="{{ old('final', $final) }}">
        </div>

        {{-- Presets de período --}}
        <div class="d-flex align-items-center gap-2 flex-wrap" style="grid-column: 1 / -1">
          <span class="text-muted small me-1">Atalhos:</span>
          <a class="chip {{ request('range')==='7d' ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['range'=>'7d']) }}"><i class="bi bi-lightning"></i> 7d</a>
          <a class="chip {{ request('range')==='15d' ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['range'=>'15d']) }}"><i class="bi bi-lightning"></i> 15d</a>
          <a class="chip {{ request('range')==='30d' ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['range'=>'30d']) }}"><i class="bi bi-lightning"></i> 30d</a>
        </div>

        <div class="filters-actions" style="grid-column: 1 / -1">
          <a class="btn btn-ghost" href="{{ url()->current() }}"><i class="bi bi-x-lg me-1"></i> Limpar</a>
          <button class="btn btn-primary"><i class="bi bi-funnel me-1"></i> Aplicar</button>
        </div>
      </form>
    </div>
  </div>

  {{-- KPIs --}}
  <div class="kpi-grid mb-3">
    <div class="kpi">
      <div class="label">Total de dias</div>
      <div class="value">{{ number_format($summary['totalDays'] ?? 0, 0, ',', '.') }}</div>
      <div class="meta">No intervalo selecionado</div>
    </div>
    <div class="kpi">
      <div class="label">Total de transações</div>
      <div class="value">{{ number_format($summary['totalTransactions'] ?? 0, 0, ',', '.') }}</div>
      <div class="meta">Somatório por dia</div>
    </div>
    <div class="kpi">
      <div class="label">Média/dia</div>
      @php
        $td = max(1, (int)($summary['totalDays'] ?? 0));
        $avg = (int)($summary['totalTransactions'] ?? 0) / $td;
      @endphp
      <div class="value">{{ number_format($avg, 0, ',', '.') }}</div>
      <div class="meta">Transações</div>
    </div>
    <div class="kpi">
      <div class="label">Maior volume (dia)</div>
      <div class="value">{{ number_format($summary['peak'] ?? 0, 0, ',', '.') }}</div>
      <div class="meta">{{ $summary['peakDate'] ?? '—' }}</div>
    </div>
  </div>

  {{-- Gráfico --}}
  <div class="card-plain mb-4">
    <div class="card-header d-flex align-items-center justify-content-between">
      <span class="fw-semibold"><i class="bi bi-graph-up-arrow me-2 text-primary"></i>Transações por dia</span>
      <div class="chart-legend">
        <span><span class="legend-dot" style="background: var(--primary)"></span> Total</span>
      </div>
    </div>
    <div class="card-body">
      <div id="dailyChartWrap">
        <canvas id="dailyChart"></canvas>
      </div>
    </div>
  </div>

  {{-- Tabela --}}
  <div class="card-plain">
    <div class="card-header d-flex align-items-center justify-content-between">
      <span class="fw-semibold"><i class="bi bi-table me-2 text-primary"></i>Detalhamento diário</span>
      <small class="text-muted">Ordenado por data</small>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead>
            <tr>
              <th style="width: 180px;">Dia</th>
              <th class="text-end" style="width: 220px;">Total de transações</th>
            </tr>
          </thead>
          <tbody>
          @forelse($rows as $r)
            <tr>
              <td class="mono">{{ $r['dia_br'] }}</td>
              <td class="text-end mono fw-semibold">{{ number_format($r['total'], 0, ',', '.') }}</td>
            </tr>
          @empty
            <tr><td colspan="2" class="text-center py-4 text-muted">Sem dados para os filtros atuais.</td></tr>
          @endforelse
          </tbody>
        </table>
      </div>
    </div>

    {{-- Paginação: só se $rows for paginator --}}
    @if ($rows instanceof \Illuminate\Contracts\Pagination\Paginator || $rows instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
      <div class="card-footer">
        {{ $rows->withQueryString()->links() }}
      </div>
    @endif
  </div>
</div>

{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js" crossorigin="anonymous"></script>
<script>
  (function(){
    const labels = @json($labels);
    const values = @json($values);

    const canvas = document.getElementById('dailyChart');
    const wrap   = document.getElementById('dailyChartWrap');
    const ctx    = canvas.getContext('2d');

    // Gradiente suave baseado na altura do container
    const grad = ctx.createLinearGradient(0, 0, 0, wrap.clientHeight || 320);
    grad.addColorStop(0, 'rgba(14,165,233,.25)');
    grad.addColorStop(1, 'rgba(14,165,233,.02)');

    new Chart(ctx, {
      type: 'line',
      data: {
        labels,
        datasets: [{
          label: 'Transações por dia',
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
          tooltip: {
            callbacks:{
              label: (ctx) => ` ${ctx.dataset.label}: ${Number(ctx.parsed.y ?? 0).toLocaleString('pt-BR')}`
            }
          }
        },
        scales: {
          x: { grid: { display:false } },
          y: {
            beginAtZero: true,
            ticks: { precision: 0, callback: v => Number(v ?? 0).toLocaleString('pt-BR') },
            grid: { color: 'rgba(0,0,0,.08)' }
          }
        }
      }
    });
  })();
</script>
@endsection
