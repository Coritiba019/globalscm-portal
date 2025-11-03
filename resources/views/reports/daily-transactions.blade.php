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
  }
  [data-theme="dark"]{
    --ink:#e7ebf0;
    --ink-weak:#c6d0da;
    --muted:#9aa3ad;
    --surface:#0f172a;
    --surface-2:#0b1220;
    --border:rgba(255,255,255,.12);
  }

  /* ————— Layout base ————— */
  .page-head{display:flex;align-items:center;justify-content:space-between;gap:1rem;margin-bottom:.75rem}
  .subtle{color:var(--muted)}
  .btn-ghost{background:var(--surface);border:1px solid var(--border);color:var(--ink)}
  .btn-ghost:hover{border-color:var(--primary);box-shadow:0 0 0 3px rgba(14,165,233,.18)}
  .context-pill{display:inline-flex;align-items:center;gap:.45rem;padding:.35rem .6rem;border-radius:999px;background:var(--surface);border:1px solid var(--border);font-weight:700}

  /* ————— Cards KPI ————— */
  .kpi{position:relative;border:1px solid var(--border);border-radius:16px;background:var(--surface);box-shadow:0 10px 28px rgba(0,0,0,.06);padding:14px}
  .kpi::before{content:"";position:absolute;inset:0 0 auto 0;height:4px;border-radius:16px 16px 0 0;background:linear-gradient(90deg,var(--primary),var(--primary-2))}
  .kpi .label{color:var(--muted);font-size:.9rem}
  .kpi .value{font-weight:800;font-size:1.6rem;color:var(--ink)}
  .kpi .meta{color:var(--muted);font-size:.85rem}

  /* ————— Cartões genéricos ————— */
  .card-plain{border:1px solid var(--border);border-radius:16px;background:var(--surface);box-shadow:0 10px 28px rgba(0,0,0,.06)}
  .card-plain .card-header{border-bottom:1px dashed var(--border);background:var(--surface)}
  .card-plain .card-footer{border-top:1px dashed var(--border);background:var(--surface)}

  /* ————— Chips ————— */
  .chip{display:inline-flex;align-items:center;gap:.4rem;padding:.35rem .6rem;border:1px solid var(--border);border-radius:999px;background:var(--surface);color:var(--ink);font-weight:600}
  .chip i{opacity:.75}
  .chip.active{border-color:#b6e3ff;box-shadow:0 0 0 3px rgba(14,165,233,.15)}

  /* ————— Gráfico ————— */
  #dailyChart{max-height:320px}
  .chart-legend{display:flex;gap:.75rem;align-items:center;flex-wrap:wrap;font-size:.9rem}
  .legend-dot{width:.75rem;height:.75rem;border-radius:999px;display:inline-block}

  /* ————— Tabela ————— */
  .table thead th{border-top:0;color:var(--ink-weak);font-weight:700;letter-spacing:.02em;background:var(--surface);position:sticky;top:0;z-index:1}
  .mono{font-variant-numeric:tabular-nums;font-family:ui-monospace,SFMono-Regular,Menlo,monospace}
  .table-hover tbody tr:hover{background:rgba(14,165,233,.06)}
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
      <form method="get" class="row g-2 align-items-end">
        <div class="col-sm-2">
          <label class="form-label">Digital</label>
          <input type="text" name="digital" class="form-control" value="{{ old('digital', $digital) }}" placeholder="ex: 1222">
        </div>
        <div class="col-sm-2">
          <label class="form-label">Tipo</label>
          @php $type = $type ?? 'todos'; @endphp
          <select name="type" class="form-select">
            <option value="todos"  {{ $type==='todos'?'selected':'' }}>Todos</option>
            <option value="credit" {{ $type==='credit'?'selected':'' }}>Crédito</option>
            <option value="debit"  {{ $type==='debit'?'selected':'' }}>Débito</option>
          </select>
        </div>
        <div class="col-sm-2">
          <label class="form-label">SubTipo</label>
          <input type="text" name="subType" class="form-control" value="{{ old('subType', $subType) }}" placeholder="PIX, TED...">
        </div>
        <div class="col-sm-2">
          <label class="form-label">Status</label>
          <input type="text" name="status" class="form-control" value="{{ old('status', $status) }}" placeholder="SUCCESS, ERROR">
        </div>
        <div class="col-sm-2">
          <label class="form-label">Inicial</label>
          <input type="date" name="initial" class="form-control" value="{{ old('initial', $initial) }}">
        </div>
        <div class="col-sm-2">
          <label class="form-label">Final</label>
          <input type="date" name="final" class="form-control" value="{{ old('final', $final) }}">
        </div>
        <div class="col-12 d-flex justify-content-end gap-2">
          <a class="btn btn-ghost" href="{{ url()->current() }}"><i class="bi bi-x-lg me-1"></i> Limpar</a>
          <button class="btn btn-primary"><i class="bi bi-funnel me-1"></i> Aplicar</button>
        </div>
      </form>
    </div>
  </div>

  {{-- KPIs --}}
  <div class="row g-3 mb-3">
    <div class="col-md-3">
      <div class="kpi h-100">
        <div class="label">Total de dias</div>
        <div class="value">{{ number_format($summary['totalDays'] ?? 0, 0, ',', '.') }}</div>
        <div class="meta">No intervalo selecionado</div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="kpi h-100">
        <div class="label">Total de transações</div>
        <div class="value">{{ number_format($summary['totalTransactions'] ?? 0, 0, ',', '.') }}</div>
        <div class="meta">Somatório por dia</div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="kpi h-100">
        <div class="label">Filtros ativos</div>
        <div class="mt-1">
          @if(!empty($filters))
            @foreach($filters as $k => $v)
              @if($v !== null && $v !== '')
                <span class="chip me-1 mb-1"><i class="bi bi-sliders"></i> {{ $k }}: <strong>{{ is_array($v)?json_encode($v):$v }}</strong></span>
              @endif
            @endforeach
          @else
            <span class="text-muted">Nenhum filtro aplicado.</span>
          @endif
        </div>
        <div class="meta mt-2">Use os campos acima para refinar o gráfico e a tabela.</div>
      </div>
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
      <canvas id="dailyChart" height="120"></canvas>
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
    const ctx = canvas.getContext('2d');

    // Gradiente suave
    const grad = ctx.createLinearGradient(0, 0, 0, canvas.height);
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
        plugins:{
          legend: { display: false },
          tooltip: {
            mode: 'index',
            intersect: false,
            callbacks:{
              label: (ctx) => ` ${ctx.dataset.label}: ${Number(ctx.parsed.y ?? 0).toLocaleString('pt-BR')}`
            }
          }
        },
        scales: {
          x: { grid: { display:false }},
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
