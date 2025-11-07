@extends('layouts.app')

@section('title', 'Transações')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800;900&display=swap" rel="stylesheet">
<style>
  :root{
    --ff:"Inter",system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Cantarell,"Helvetica Neue",Arial,"Noto Sans";
    --ink:#0f172a; --muted:#64748b; --muted-2:#94a3b8;
    --surface:#ffffff; --surface-2:#f7f8fb; --card:#ffffff; --border:#e5e7eb;
    --primary:#0ea5e9; --primary-2:#3abff8; --success:#16a34a; --danger:#ef4444; --warning:#f59e0b;

    --fz-xxs: clamp(.76rem, 2vw, .84rem);
    --fz-xs:  clamp(.85rem, 2.2vw, .94rem);
    --fz-sm:  clamp(.95rem, 2.4vw, 1.05rem);
    --fz-md:  clamp(1.08rem, 3vw, 1.22rem);
    --fz-lg:  clamp(1.22rem, 3.5vw, 1.45rem);

    --shadow-sm: 0 8px 22px rgba(0,0,0,.06);
    --shadow-md: 0 12px 28px rgba(0,0,0,.10);
  }
  [data-bs-theme="dark"], .theme-dark{
    --ink:#e7ebf0; --muted:#a9b3c6; --muted-2:#94a3b8;
    --surface:#0b1020; --surface-2:#0a0f1d; --card:#0e1528; --border:rgba(255,255,255,.10);
    --primary:#38bdf8; --primary-2:#22d3ee; --success:#22c55e; --warning:#fbbf24; --danger:#f87171;
    --shadow-sm: 0 8px 22px rgba(0,0,0,.35);
    --shadow-md: 0 12px 28px rgba(0,0,0,.45);
  }

  html,body{ font-family:var(--ff); background:var(--surface-2); color:var(--ink) }

  .subtle{ color:var(--muted); font-size:var(--fz-xs) }

  /* ===== Card base (dark refinado) ===== */
  .card-plain{
    border:1px solid var(--border);
    border-radius:16px;
    background:
      radial-gradient(1200px 600px at -10% -10%, color-mix(in srgb, var(--primary) 6%, transparent), transparent 40%),
      radial-gradient(900px 500px at 110% -20%, color-mix(in srgb, var(--primary-2) 5%, transparent), transparent 40%),
      var(--card);
    box-shadow:var(--shadow-sm);
  }
  .card-plain .card-header{
    border-bottom:1px dashed var(--border);
    padding:.85rem 1rem;
    background:transparent;
    font-weight:800;
    display:flex; align-items:center; justify-content:space-between; gap:.75rem;
  }
  .card-plain .card-footer{
    border-top:1px dashed var(--border);
    background:transparent;
  }

  .btn-ghost{
    background:color-mix(in srgb, var(--card) 85%, var(--surface-2));
    border:1px solid var(--border);
    color:var(--ink); font-weight:800;
  }
  .btn-ghost:hover{ border-color:var(--primary); box-shadow:0 0 0 3px rgba(14,165,233,.18) }

  /* ===== Chips / contextos ===== */
  .pill-ctx{
    display:inline-flex; align-items:center; gap:.45rem; padding:.35rem .65rem;
    border:1px solid var(--border); border-radius:999px;
    background:color-mix(in srgb, var(--primary) 7%, transparent);
    color:color-mix(in srgb, var(--primary) 92%, #07111f); font-weight:800; font-size:var(--fz-xxs)
  }

  /* ===== KPIs ===== */
  .kpi-grid{ display:grid; gap:.8rem }
  @media (min-width: 540px){ .kpi-grid{ grid-template-columns: repeat(2,1fr) } }
  @media (min-width: 992px){ .kpi-grid{ grid-template-columns: repeat(4,1fr) } }

  .kpi{
    position:relative; border:1px solid var(--border); border-radius:16px;
    background:var(--card); padding:14px; box-shadow:var(--shadow-sm)
  }
  .kpi::before{
    content:""; position:absolute; inset:0 0 auto 0; height:4px; border-radius:16px 16px 0 0;
    background:linear-gradient(90deg,var(--primary),var(--primary-2))
  }
  .kpi .label{ color:var(--muted); font-size:var(--fz-xxs) }
  .kpi .value{ font-weight:900; font-size:var(--fz-lg) }
  .kpi .meta{ color:var(--muted); font-size:var(--fz-xxs) }

  /* ===== Filtros ===== */
  .filters-grid{ display:grid; gap:.6rem }
  @media (min-width: 768px){ .filters-grid{ grid-template-columns: repeat(6, 1fr) } }
  .filters-actions{ display:flex; justify-content:flex-end; gap:.5rem; flex-wrap:wrap }

  .chip{
    display:inline-flex; align-items:center; gap:.4rem; padding:.35rem .6rem; border:1px solid var(--border);
    border-radius:999px; background:var(--card); color:var(--ink); font-weight:800; font-size:var(--fz-xxs)
  }
  .chip i{ opacity:.8 }
  .chip.active{ border-color:color-mix(in srgb, var(--primary) 35%, var(--border)); box-shadow:0 0 0 3px rgba(14,165,233,.15) }

  /* ===== Tabela ===== */
  .table thead th{
    border-top:0; color:var(--muted); background:var(--card); font-weight:900; letter-spacing:.02em;
    position:sticky; top:0; z-index:1; font-size:var(--fz-xxs)
  }
  .table-header-shadow:before{
    content:""; position:absolute; left:0; right:0; bottom:-1px; height:16px; pointer-events:none;
    background:linear-gradient(to bottom, rgba(0,0,0,.12), transparent);
    opacity:0; transition:opacity .2s ease;
  }
  .table-scrolled .table-header-shadow:before{ opacity:.25 }

  .table td{ font-size:var(--fz-xs) }
  .table-hover tbody tr:hover{ background:color-mix(in srgb, var(--primary) 7%, transparent) }

  .zebra tbody tr:nth-child(odd){ background:color-mix(in srgb, var(--surface) 90%, var(--card)) }
  [data-bs-theme="dark"] .zebra tbody tr:nth-child(odd){ background:rgba(255,255,255,.02) }

  .mono{ font-variant-numeric:tabular-nums; font-family:ui-monospace,SFMono-Regular,Menlo,monospace }
  .badge-s{
    font-size:.72rem; font-weight:900; border:1px solid var(--border);
    background:color-mix(in srgb, var(--primary) 10%, transparent);
    color:color-mix(in srgb, var(--primary) 90%, #061018);
    padding:.2rem .55rem; border-radius:999px
  }

  /* tipo/status */
  .pill{ display:inline-flex; align-items:center; gap:.35rem; padding:.22rem .55rem; border-radius:999px; font-weight:900; font-size:.72rem; border:1px solid var(--border) }
  .pill.type-c{ background:color-mix(in srgb, var(--success) 10%, transparent); color:color-mix(in srgb, var(--success) 92%, #061018) }
  .pill.type-d{ background:color-mix(in srgb, var(--danger) 10%, transparent);  color:color-mix(in srgb, var(--danger) 92%, #061018) }
  .pill.st-success{ background:color-mix(in srgb, var(--success) 10%, transparent); color:color-mix(in srgb, var(--success) 92%, #061018) }
  .pill.st-error{ background:color-mix(in srgb, var(--danger) 10%, transparent);  color:color-mix(in srgb, var(--danger) 92%, #061018) }
  .pill.st-pending{ background:color-mix(in srgb, var(--warning) 10%, transparent); color:color-mix(in srgb, var(--warning) 92%, #061018) }

  .val-credit{ color: color-mix(in srgb, var(--success) 92%, #061018) }
  .val-debit{  color: color-mix(in srgb, var(--danger)  92%, #061018) }

  /* Botão copiar EndToEnd */
  .copy-btn{
    border:1px dashed var(--border);
    background:transparent; border-radius:8px; padding:.1rem .35rem; font-size:.72rem; font-weight:800;
    color:var(--muted);
  }
  .copy-btn:hover{ color:var(--primary); border-color:var(--primary) }

  /* Print */
  @media print{
    .page-toolbar .btn, .card-header .btn, .chip, .filters-actions{ display:none !important }
    .card-plain, .kpi{ box-shadow:none !important; background:#fff !important }
    body{ background:#fff }
  }
</style>
@endpush

@section('content')
<div class="container py-4">

  {{-- Toolbar / Cabeçalho --}}
  <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2 page-toolbar">
    <div>
      <h2 class="h4 mb-1">Transações</h2>
      <div class="subtle d-flex align-items-center gap-2 flex-wrap">
        <span class="pill-ctx"><i class="bi bi-bank2"></i> Digital <strong class="mono">{{ $digital }}</strong></span>
        <span class="pill-ctx"><i class="bi bi-calendar3"></i> {{ $initial }} — {{ $final }}</span>
      </div>
    </div>
    <div class="d-flex gap-2">
      <a class="btn btn-ghost btn-sm" href="{{ route('select-account') }}"><i class="bi bi-bank me-1"></i> Trocar conta</a>
      <button class="btn btn-ghost btn-sm" onclick="window.print()"><i class="bi bi-printer me-1"></i> Imprimir</button>
    </div>
  </div>

  {{-- Alertas --}}
  @if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
  @endif

  {{-- Filtros --}}
  <div class="card-plain mb-3">
    <div class="card-header">
      <span><i class="bi bi-funnel me-2 text-primary"></i>Filtros</span>
      <div class="d-none d-md-flex gap-2">
        <a class="chip {{ request('range')==='7d' ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['range'=>'7d']) }}"><i class="bi bi-lightning"></i> 7d</a>
        <a class="chip {{ request('range')==='15d' ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['range'=>'15d']) }}"><i class="bi bi-lightning"></i> 15d</a>
        <a class="chip {{ request('range')==='30d' ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['range'=>'30d']) }}"><i class="bi bi-lightning"></i> 30d</a>
      </div>
    </div>
    <div class="card-body">
      <form method="get" class="filters-grid">
        <input type="hidden" name="digital" value="{{ $digital }}">

        <div>
          <label class="form-label">Inicial</label>
          <input type="date" name="initial" class="form-control" value="{{ old('initial', $initial) }}">
        </div>
        <div>
          <label class="form-label">Final</label>
          <input type="date" name="final" class="form-control" value="{{ old('final', $final) }}">
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
          <input type="text" name="status" class="form-control" value="{{ old('status', $status) }}" placeholder="SUCCESS, ERROR...">
        </div>
        <div>
          <label class="form-label">Por página</label>
          <select name="limit" class="form-select">
            @foreach([10,25,50,100,200,500,1000] as $opt)
              <option value="{{ $opt }}" {{ ((int)$limit)===$opt ? 'selected' : '' }}>{{ $opt }}</option>
            @endforeach
          </select>
        </div>

        <div class="filters-actions" style="grid-column: 1 / -1">
          <a class="btn btn-ghost" href="{{ url()->current() . '?digital=' . $digital }}"><i class="bi bi-x-lg me-1"></i> Limpar</a>
          <button class="btn btn-primary"><i class="bi bi-funnel me-1"></i> Aplicar</button>
        </div>
      </form>
    </div>
  </div>

  {{-- KPIs --}}
  <div class="kpi-grid mb-3">
    <div class="kpi">
      <div class="label">Saldo</div>
      <div class="value">R$ {{ number_format((float)($summary['balance'] ?? 0), 2, ',', '.') }}</div>
      @if(!empty($summary['dtBalance']))
        <div class="meta mono mt-1">Atualizado: {{ \Illuminate\Support\Carbon::parse($summary['dtBalance'])->tz('America/Sao_Paulo')->format('d/m/Y H:i') }}</div>
      @endif
    </div>
    <div class="kpi">
      <div class="label">Créditos no período</div>
      <div class="value val-credit">R$ {{ number_format((float)($summary['credit'] ?? 0), 2, ',', '.') }}</div>
    </div>
    <div class="kpi">
      <div class="label">Débitos no período</div>
      <div class="value val-debit">R$ {{ number_format((float)($summary['debit'] ?? 0), 2, ',', '.') }}</div>
    </div>
    <div class="kpi">
      <div class="label">Transações retornadas</div>
      <div class="value">{{ number_format((int)($summary['count'] ?? 0), 0, ',', '.') }}</div>
      <div class="meta">de {{ number_format((int)($summary['total'] ?? 0), 0, ',', '.') }}</div>
    </div>
  </div>

  {{-- Tabela --}}
  <div class="card-plain">
    <div class="card-header table-header-shadow">
      <span class="fw-semibold"><i class="bi bi-table me-2 text-primary"></i>Lista de transações</span>
      <div class="d-flex align-items-center gap-2">
        <span class="chip">
          Página {{ number_format((int)($paginator->currentPage() ?? 1), 0, ',', '.') }}
          · Itens: {{ number_format((int)($paginator->count() ?? count($rows)), 0, ',', '.') }}
        </span>
      </div>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive zebra" id="txTableWrap" style="position:relative; max-height:64vh; overflow:auto;">
        <table class="table table-hover align-middle mb-0">
          <thead>
            <tr>
              <th style="min-width:165px">Data/Hora</th>
              <th>Tipo</th>
              <th>SubTipo</th>
              <th>Status</th>
              <th class="text-end" style="min-width:140px">Valor</th>
              <th style="min-width:220px">Pagador</th>
              <th style="min-width:220px">Recebedor</th>
              <th style="min-width:200px">EndToEnd</th>
            </tr>
          </thead>
          <tbody>
          @forelse($rows as $t)
            @php
              $dt    = $t['dtHrTransaction'] ?? $t['dt_hr_transaction'] ?? null;
              $type  = strtoupper((string)($t['type'] ?? ''));
              $st    = strtoupper((string)($t['status'] ?? ''));
              $val   = (float) ($t['amount'] ?? 0);
              $payer = $t['payer'] ?? [];
              $rcv   = $t['receiver'] ?? [];
              $isCredit = ($type === 'C');
              $valClass = $isCredit ? 'val-credit' : ($type === 'D' ? 'val-debit' : '');
            @endphp
            <tr>
              <td class="mono">
                @if($dt) {{ \Illuminate\Support\Carbon::parse($dt)->tz('America/Sao_Paulo')->format('d/m/Y H:i') }} @else — @endif
              </td>
              <td>
                @if($type === 'C') <span class="pill type-c"><i class="bi bi-arrow-down-left"></i> Crédito</span>
                @elseif($type === 'D') <span class="pill type-d"><i class="bi bi-arrow-up-right"></i> Débito</span>
                @else <span class="pill">{{ $type ?: '—' }}</span>@endif
              </td>
              <td><span class="badge-s">{{ $t['subType'] ?? '—' }}</span></td>
              <td>
                @if($st === 'SUCCESS') <span class="pill st-success"><i class="bi bi-check2-circle"></i> SUCCESS</span>
                @elseif($st === 'ERROR') <span class="pill st-error"><i class="bi bi-x-octagon"></i> ERROR</span>
                @else <span class="pill st-pending"><i class="bi bi-hourglass-split"></i> {{ $st ?: '—' }}</span>@endif
              </td>
              <td class="text-end mono fw-bold {{ $valClass }}">R$ {{ number_format($val, 2, ',', '.') }}</td>
              <td>
                @if(!empty($payer))
                  <div class="small fw-semibold">{{ $payer['name'] ?? '—' }}</div>
                  <div class="small text-muted mono">
                    {{ $payer['bank'] ?? '-' }} · Ag {{ $payer['agency'] ?? '-' }} · {{ $payer['accountNumber'] ?? '-' }}
                  </div>
                @else —
                @endif
              </td>
              <td>
                @if(!empty($rcv))
                  <div class="small fw-semibold">{{ $rcv['name'] ?? '—' }}</div>
                  <div class="small text-muted mono">
                    {{ $rcv['bank'] ?? '-' }} · Ag {{ $rcv['agency'] ?? '-' }} · {{ $rcv['accountNumber'] ?? '-' }}
                  </div>
                @else —
                @endif
              </td>
              {{-- EndToEnd agora por último --}}
              <td class="mono">
                <div class="d-flex align-items-center gap-2">
                  <span title="{{ $t['endToEnd'] ?? '' }}">{{ \Illuminate\Support\Str::limit($t['endToEnd'] ?? '—', 28) }}</span>
                  @if(!empty($t['endToEnd']))
                    <button class="copy-btn" type="button" data-copy="{{ $t['endToEnd'] }}"><i class="bi bi-clipboard"></i></button>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="8" class="text-center py-4 text-muted">Sem dados para os filtros atuais.</td></tr>
          @endforelse
          </tbody>
        </table>
      </div>
    </div>

    {{-- paginação + resumo --}}
    <div class="card-footer d-flex align-items-center justify-content-between flex-wrap gap-2">
      <div class="subtle">
        Exibindo
        <strong>{{ number_format((int)($paginator->count() ?? count($rows)), 0, ',', '.') }}</strong>
        de
        <strong>{{ number_format((int)($summary['total'] ?? 0), 0, ',', '.') }}</strong>
        registros no período
      </div>
      <div>
        @if($paginator instanceof \Illuminate\Contracts\Pagination\Paginator || $paginator instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
          {{ $paginator->withQueryString()->onEachSide(1)->links('pagination::bootstrap-5') }}
        @endif
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  // sombra no header da tabela quando houver scroll
  (function(){
    const wrap = document.getElementById('txTableWrap');
    const cardHeader = wrap?.closest('.card-plain')?.querySelector('.card-header');
    if(!wrap || !cardHeader) return;
    function onScroll(){
      const scrolled = wrap.scrollTop > 0;
      cardHeader.parentElement.classList.toggle('table-scrolled', scrolled);
    }
    wrap.addEventListener('scroll', onScroll, { passive:true });
    onScroll();
  })();

  // copiar EndToEnd
  (function(){
    document.addEventListener('click', async (e) => {
      const btn = e.target.closest('.copy-btn');
      if(!btn) return;
      const value = btn.getAttribute('data-copy') || '';
      try{
        await navigator.clipboard.writeText(value);
        btn.innerHTML = '<i class="bi bi-clipboard-check"></i>';
        setTimeout(() => btn.innerHTML = '<i class="bi bi-clipboard"></i>', 1200);
      }catch(err){ console.warn('Copy failed', err); }
    });
  })();
</script>
@endpush
