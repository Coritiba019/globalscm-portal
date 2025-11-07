@extends('layouts.app')

@section('title', 'Selecionar conta')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800;900&display=swap" rel="stylesheet">
<style>
  /* ============== Tokens base (light) ============== */
  :root{
    --ff-base:"Inter",system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Cantarell,"Helvetica Neue",Arial,"Noto Sans";
    --bg:#fff; --bg-soft:#f8fafc; --card-bg:#fff; --card-elev:#fff; --border:#e5e7eb;
    --text:#0f172a; --muted:#64748b; --muted-2:#94a3b8;
    --primary:#0ea5e9; --primary-2:#3abff8; --accent:#22c55e; --warning:#f59e0b; --danger:#ef4444;
    --ring:0 0 0 3px rgba(14,165,233,.18); --ring-strong:0 0 0 4px rgba(14,165,233,.25);
    --shadow-xxs:0 1px 6px rgba(0,0,0,.04); --shadow-xs:0 2px 10px rgba(0,0,0,.06);
    --shadow-sm:0 4px 14px rgba(0,0,0,.08); --shadow-md:0 10px 26px rgba(0,0,0,.12);
    --radius-sm:10px; --radius-md:12px; --radius-lg:16px;
    --fz-xxs:clamp(.72rem,1.2vw,.8rem);
    --fz-xs: clamp(.8rem,1.4vw,.88rem);
    --fz-sm: clamp(.9rem,1.6vw,.96rem);
    --fz-md: clamp(1rem,1.8vw,1.06rem);
    --fz-lg: clamp(1.12rem,2.2vw,1.26rem);
    --fz-xl: clamp(1.24rem,2.6vw,1.42rem);
  }

  /* ============== Dark theme melhorado ============== */
  [data-bs-theme="dark"], .theme-dark{
    --bg:#0a0f1e; --bg-soft:#0c1426;
    --card-bg:#0f162b; --card-elev:#0c1224; --border:#1e2a46;
    --text:#e6edf7; --muted:#aab6d1; --muted-2:#90a0bf;
    --primary:#47c1ff; --primary-2:#22d3ee; --accent:#34d399; --warning:#fbbf24; --danger:#f87171;
    --ring:0 0 0 3px rgba(71,193,255,.25); --ring-strong:0 0 0 4px rgba(71,193,255,.35);
    --shadow-xxs:0 1px 6px rgba(0,0,0,.28); --shadow-xs:0 2px 10px rgba(0,0,0,.36);
    --shadow-sm:0 8px 20px rgba(0,0,0,.45); --shadow-md:0 16px 30px rgba(0,0,0,.55);
  }

  /* ============== Base ============== */
  *,*::before,*::after{ box-sizing:border-box; }
  html,body{ font-family:var(--ff-base); }
  body{ background:var(--bg-soft); color:var(--text); }
  .text-secondary{ color:var(--muted)!important; }
  .card,.card-body{ background:var(--card-bg); border-color:var(--border); }
  .alert{ border-color:var(--border); }
  .alert-danger{ background:color-mix(in srgb,var(--danger) 10%,var(--card-bg)); color:color-mix(in srgb,var(--danger) 90%,#fff); }
  .alert-warning{ background:color-mix(in srgb,var(--warning) 10%,var(--card-bg)); color:color-mix(in srgb,var(--warning) 92%,#fff); }

  .form-control,.form-select,.input-group-text{
    background:var(--card-bg); color:var(--text); border-color:var(--border);
    transition:background .16s,border-color .16s,color .16s,box-shadow .16s;
  }
  .form-control:focus,.form-select:focus{ border-color:color-mix(in srgb,var(--primary) 52%,var(--border)); box-shadow:var(--ring); background:var(--card-elev); }
  .form-control::placeholder{ color:var(--muted-2); }

  .btn-primary{ background:linear-gradient(135deg,var(--primary),var(--primary-2)); border:none; color:#07111f; font-weight:800; }
  .btn-primary:hover{ filter:brightness(1.06); }
  .btn-outline-secondary{ border-color:var(--border); color:var(--text); }
  .btn-outline-secondary:hover{ border-color:var(--primary); box-shadow:var(--ring); }

  /* ============== Grid externo ============== */
  .acc-grid{
    display:grid; gap:clamp(.8rem,2vw,1rem);
    grid-template-columns:repeat(auto-fit,minmax(320px,1fr));
    align-items:start;
  }

  /* ============== Card ============== */
  .acc-card{
    position:relative; isolation:isolate; overflow:hidden;
    border:1px solid var(--border); border-radius:var(--radius-lg);
    background:var(--card-bg); box-shadow:var(--shadow-xs);
    transition:transform .16s, box-shadow .16s, border-color .16s, background .16s;
    display:grid; grid-template-areas:"header" "meta" "balance" "actions";
    grid-template-rows:auto auto auto auto;
    container-type:inline-size;
  }
  .acc-card::before{
    content:""; position:absolute; inset:-1px; border-radius:inherit; padding:1px;
    background:linear-gradient(135deg, color-mix(in srgb,var(--primary) 28%,transparent) 0%, color-mix(in srgb,var(--accent) 20%,transparent) 45%, transparent 100%);
    -webkit-mask:linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
    -webkit-mask-composite:xor; mask-composite:exclude; opacity:0; transition:opacity .16s; z-index:-1;
  }
  .acc-card:hover{ transform:translateY(-1px); box-shadow:var(--shadow-sm); }
  .acc-card:hover::before{ opacity:.9; }
  .acc-card.selected{ border-color:color-mix(in srgb,var(--primary) 44%,var(--border)); box-shadow:var(--ring),var(--shadow-xs); }

  /* 2 colunas quando o PRÓPRIO card couber */
  @container (min-width: 700px){
    .acc-card{
      grid-template-columns: 1fr minmax(220px, 38%);
      grid-template-areas:
        "header  actions"
        "meta    actions"
        "balance actions";
      grid-auto-rows:min-content;
    }
  }

  /* ===== Header ===== */
  .acc-header{
    grid-area:header; display:grid;
    grid-template-columns:auto minmax(0,1fr) auto;
    align-items:start; gap:.7rem; padding:.8rem .95rem;
    border-bottom:1px dashed var(--border);
  }
  .acc-avatar{
    width:40px;height:40px;border-radius:12px;display:grid;place-items:center;font-weight:900;color:#061018;
    background:linear-gradient(135deg,var(--primary),var(--primary-2));
    box-shadow:inset 0 0 0 2px rgba(255,255,255,.22),var(--shadow-xxs); font-size:.95rem;
  }
  [data-bs-theme="dark"] .acc-avatar,.theme-dark .acc-avatar{ color:#07111f; }
  .acc-head-col{ min-width:0; }
  .acc-title{ font-size:var(--fz-md); font-weight:900; line-height:1.12; letter-spacing:.01em; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }

  /* ===== map-badge: SOMENTE NOME ===== */
  .acc-map{ margin:.28rem 0 .1rem; }
  .map-badge{
    display:inline-flex; align-items:center; gap:.42rem; padding:.24rem .5rem;
    border-radius:999px; border:1px solid color-mix(in srgb,var(--primary) 30%,var(--border));
    background:color-mix(in srgb,var(--primary) 10%,var(--bg));
    color:color-mix(in srgb,var(--primary) 94%,var(--text));
    font-weight:800; font-size:clamp(.72rem,.95vw,.86rem);
    max-width:100%; overflow:hidden; white-space:nowrap;
  }
  .map-badge .label-acc{ min-width:0; overflow:hidden; text-overflow:ellipsis; }
  .map-badge .bi{ font-size:.86rem; opacity:.9; }

  .acc-sub{ color:var(--muted); font-size:var(--fz-xxs); }
  .acc-chip{
    display:inline-flex; align-items:center; gap:.38rem; padding:.32rem .55rem; border-radius:999px;
    border:1px solid color-mix(in srgb,var(--primary) 35%,var(--border));
    background:color-mix(in srgb,var(--primary) 12%,transparent);
    color:color-mix(in srgb,var(--primary) 94%,var(--text));
    font-weight:800; font-size:.78rem; white-space:nowrap; max-width:100%;
    overflow:hidden; text-overflow:ellipsis; justify-self:end;
  }
  @media (max-width:520px){ .acc-chip{ grid-column:1 / -1; justify-self:start; } }

  /* ===== Meta ===== */
  .acc-meta{ grid-area:meta; padding:.65rem .95rem; display:grid; gap:.55rem; grid-template-columns:1fr; }
  @container (min-width: 560px){ .acc-meta{ grid-template-columns:repeat(2,1fr); } }
  @container (min-width: 880px){ .acc-meta{ grid-template-columns:repeat(3,1fr); } }

  /* Linha KV com mais espaço pro valor (2 colunas) */
  .kv{
    display:grid; grid-template-columns:auto 1fr;
    align-items:center; column-gap:.6rem;
    padding:.5rem .65rem; border:1px solid var(--border);
    border-radius:var(--radius-md); background:var(--card-elev);
    transition:border-color .16s,box-shadow .16s,background .16s; min-height:42px;
  }
  .kv:focus-within{ border-color:var(--primary); box-shadow:var(--ring); }

  .kv-icon{
    width:28px;height:28px;border-radius:9px;display:grid;place-items:center;border:1px solid var(--border);
    background:color-mix(in srgb,var(--card-bg) 88%,var(--bg)); box-shadow:var(--shadow-xxs);
    margin-right:.1rem;
  }
  .kv-icon .bi{ font-size:.95rem; color:color-mix(in srgb,var(--primary) 65%,var(--muted)); }
  .kv-key{ color:var(--muted); font-size:var(--fz-xxs); margin-left:.1rem; }

  /* VALOR: sem ellipsis, fonte reduz automaticamente e alinha à direita */
  .kv-val{
    justify-self:end;
    max-width:100%;
    overflow:visible;
    white-space:nowrap;      /* 1 linha */
    text-overflow:clip;      /* sem "..." */
    font-size:clamp(.78rem, 1.35vw, .95rem);
    line-height:1.05;
    font-weight:800;
    letter-spacing:.01em;
    color:var(--text);
    text-align:right;
  }
  .kv-val.mono{
    font-variant-numeric:tabular-nums;
    font-family:ui-monospace,SFMono-Regular,Menlo,monospace;
    font-size:clamp(.76rem, 1.25vw, .92rem);
  }
  /* quebra suave opcional para casos MUITO longos */
  @supports (overflow-wrap:anywhere){
    .kv-val.too-long { overflow-wrap:anywhere; white-space:normal; text-align:left; }
  }

  /* ===== Saldo ===== */
  .acc-balance-wrap{ grid-area:balance; padding:.25rem .95rem .75rem; }
  .acc-balance-label{ display:flex; align-items:center; gap:.35rem; color:var(--muted); font-size:var(--fz-xxs); }
  .acc-balance-label .bi{ font-size:.88rem; color:color-mix(in srgb,var(--accent) 70%,var(--muted)); }
  .acc-balance{ font-size:var(--fz-lg); font-weight:900; display:flex; gap:.28rem; flex-wrap:wrap; line-height:1.1; }
  .acc-balance small{ color:var(--muted); font-weight:600; font-size:var(--fz-xxs); }

  /* ===== Actions ===== */
  .acc-actions{ grid-area:actions; padding:.7rem .95rem .95rem; border-top:1px dashed var(--border); }
  @container (min-width: 700px){
    .acc-actions{ border-top:none; border-left:1px dashed var(--border); padding-left:1rem; }
  }
  .acc-actions-grid{ display:grid; gap:.5rem; grid-template-columns:1fr; align-items:start; }
  @container (min-width: 560px){
    .acc-actions-grid{ grid-template-columns:1fr 1fr; }
    .acc-actions-grid .primary{ grid-column:1 / -1; }
  }
  @container (min-width: 700px){
    .acc-actions-grid{ grid-template-columns:1fr; }
    .acc-actions-grid .primary{ grid-column:auto; }
  }
  .btn-ghost,.btn-primary-strong{
    min-height:40px; display:inline-flex; align-items:center; justify-content:center; gap:.42rem;
    font-size:var(--fz-xs); font-weight:800; max-width:100%; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;
  }
  .btn-ghost{ background:color-mix(in srgb,var(--card-bg) 94%,var(--bg)); border:1px solid var(--border); color:var(--text); }
  .btn-ghost:hover{ border-color:var(--primary); box-shadow:var(--ring); }
  .btn-primary-strong{ background:linear-gradient(135deg,var(--primary),var(--primary-2)); color:#07111f; border:none; }
  .btn-primary-strong:hover{ filter:brightness(1.06); }
  .btn-primary-strong:focus-visible{ box-shadow:var(--ring-strong); }

  /* Paginação */
  .pagination .page-link{ background:var(--card-bg); color:var(--text); border-color:var(--border); }
  .pagination .page-link:hover{ border-color:var(--primary); box-shadow:var(--ring); }
  .pagination .active .page-link{ background:linear-gradient(135deg,var(--primary),var(--primary-2)); color:#07111f; border:none; }

  @media (prefers-reduced-motion:reduce){
    .acc-card, .acc-card::before, .form-control, .form-select, .btn{ transition:none!important; }
    .acc-card:hover{ transform:none!important; }
  }
</style>
@endpush

@section('content')
@php
  use App\Support\AccountLabels;
  use Illuminate\Support\Facades\Route as R;
@endphp

<div class="container-xl">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <div>
      <h1 class="h3 mb-1" style="font-size:clamp(1.02rem,4vw,1.25rem);">Selecionar conta</h1>
      <div class="text-secondary" style="font-size:clamp(.82rem,2.6vw,.95rem);">Escolha a conta digital para usar no painel</div>
    </div>
    @php $selected = session('digital_account_id'); @endphp
    @if ($selected)
      <span class="badge rounded-pill px-3 py-2" style="background:color-mix(in srgb,var(--primary) 12%,transparent); border:1px solid color-mix(in srgb,var(--primary) 32%,var(--border)); color:color-mix(in srgb,var(--primary) 88%,var(--text)); font-weight:800;">
        <i class="bi bi-check2-circle me-1"></i> Conta ativa #{{ $selected }}
      </span>
    @endif
  </div>

  @if (session('error'))
    <div class="alert alert-danger mb-3" role="alert">
      <i class="bi bi-exclamation-octagon-fill me-2"></i>{{ session('error') }}
    </div>
  @endif
  @if ($pendingMsg ?? false)
    <div class="alert alert-warning mb-3" role="alert">
      <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ $pendingMsg }}
    </div>
  @endif

  <div class="card filter-sticky mb-3">
    <form method="GET" action="{{ route('select-account') }}" class="row g-2 align-items-stretch">
      <div class="col-12 col-md-7">
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-search"></i></span>
          <input type="text" class="form-control" name="q" value="{{ $q }}" placeholder="Buscar por conta, agência, empresa ou rótulo..." inputmode="search" autocomplete="off" aria-label="Buscar conta">
        </div>
      </div>
      <div class="col-6 col-md-2">
        <select name="limit" class="form-select" aria-label="Resultados por página">
          @foreach ([10,25,50,100] as $opt)
            <option value="{{ $opt }}" @selected(($limit ?? 25)==$opt)>{{ $opt }} / pág.</option>
          @endforeach
        </select>
      </div>
      <div class="col-6 col-md-3 d-grid d-md-flex gap-2">
        <button class="btn btn-primary w-100 w-md-auto">
          <i class="bi bi-funnel-fill me-1"></i> Filtrar
        </button>
        <a href="{{ route('select-account') }}" class="btn btn-outline-secondary w-100 w-md-auto">
          <i class="bi bi-x-lg me-1"></i> Limpar
        </a>
      </div>
    </form>
  </div>

  @php $hasAcc = is_iterable($accounts ?? []) && count($accounts) > 0; @endphp

  @if (!$hasAcc)
    <div class="card card-md shadow-sm">
      <div class="card-body text-center">
        <div class="h2 mb-1" style="font-size:clamp(1.02rem,4vw,1.32rem);">Nenhuma conta encontrada</div>
        <p class="text-secondary mb-0">Ajuste os filtros ou verifique se há contas vinculadas à sua empresa.</p>
      </div>
    </div>
  @else
    <div class="acc-grid">
      @foreach ($accounts as $acc)
        @php
          $id      = (int) data_get($acc, 'digital_account_id');
          $agency  = (string) data_get($acc, 'agency', '0001');
          $account = (string) data_get($acc, 'account', '');
          $balance = data_get($acc, '__balance');
          $dtBal   = data_get($acc, '__dt_balance');
          $company = trim((string) data_get($acc, 'company.name', '—'));
          $label   = (string) data_get($acc, '__label') ?: AccountLabels::label($account);
          $isSelected = session('digital_account_id') == $id;
          $initial = strtoupper(mb_substr($company !== '' ? $company : 'C', 0, 1));
          $accountTooLong = strlen($account ?? '') > 18; /* quebra suave se for MUITO longo */
        @endphp

        <div class="acc-card {{ $isSelected ? 'selected' : '' }}">
          {{-- HEADER --}}
          <div class="acc-header">
            <div class="acc-avatar" aria-hidden="true">{{ $initial }}</div>
            <div class="acc-head-col">
              <div class="acc-title" title="{{ $company }}">{{ $company ?: '—' }}</div>

              @if($account)
                <div class="acc-map" aria-label="Identificador mapeado">
                  <span class="map-badge" title="Nome mapeado da conta">
                    <i class="bi bi-tag-fill"></i>
                    <span class="label-acc">{{ $label }}</span>
                  </span>
                </div>
              @endif

              <div class="acc-sub"><i class="bi bi-hash me-1"></i>ID #{{ $id }}</div>
            </div>

            <span class="acc-chip">
              @if($isSelected) <i class="bi bi-check2-circle"></i> Selecionada
              @else <i class="bi bi-bank2"></i> Disponível @endif
            </span>
          </div>

          {{-- META --}}
          <div class="acc-meta">
            <div class="kv" title="Agência">
              <span class="kv-icon"><i class="bi bi-buildings-fill"></i></span>
              <div class="kv-key">Agência</div>
              <div class="kv-val mono">{{ $agency ?: '—' }}</div>
            </div>

            <div class="kv" title="Conta">
              <span class="kv-icon"><i class="bi bi-credit-card-2-back-fill"></i></span>
              <div class="kv-key">Conta</div>
              <div class="kv-val mono {{ $accountTooLong ? 'too-long' : '' }}">
                {{ $account ?: '—' }} {{-- número completo aqui --}}
              </div>
            </div>

            <div class="kv" title="Empresa">
              <span class="kv-icon"><i class="bi bi-briefcase-fill"></i></span>
              <div class="kv-key">Empresa</div>
              <div class="kv-val" title="{{ $company }}">{{ $company ?: '—' }}</div>
            </div>
          </div>

          {{-- SALDO --}}
          <div class="acc-balance-wrap">
            <div class="acc-balance-label"><i class="bi bi-cash-coin"></i> Saldo</div>
            <div class="acc-balance">
              @if(!is_null($balance))
                R$ {{ number_format((float)$balance, 2, ',', '.') }}
                @if($dtBal)
                  <small>· {{ \Illuminate\Support\Carbon::parse($dtBal)->tz('America/Sao_Paulo')->format('d/m H:i') }}</small>
                @endif
              @else — @endif
            </div>
          </div>

          {{-- ACTIONS --}}
          <div class="acc-actions">
            <div class="acc-actions-grid">
              <form method="POST" action="{{ route('select-account.store') }}" class="d-grid primary">
                @csrf
                <input type="hidden" name="digital_account_id" value="{{ $id }}">
                <input type="hidden" name="agency" value="{{ $agency }}">
                <input type="hidden" name="account" value="{{ $account }}">
                <button type="submit" class="btn btn-primary-strong btn-sm w-100" aria-label="Usar conta {{ $account ?: $id }}">
                  <i class="bi bi-check2-circle"></i> Usar esta conta
                </button>
              </form>

              <a class="btn btn-ghost btn-sm" href="{{ route('reports.daily-transactions', ['digital' => $id]) }}" aria-label="Ver transações diárias da conta {{ $account ?: $id }}">
                <i class="bi bi-graph-up-arrow"></i> Transações diárias
              </a>

              @if (R::has('transactions.index'))
                <a class="btn btn-ghost btn-sm" href="{{ route('transactions.index', ['digital' => $id]) }}" aria-label="Ver lista de transações da conta {{ $account ?: $id }}">
                  <i class="bi bi-card-list"></i> Transações
                </a>
              @else
                <a class="btn btn-ghost btn-sm" href="{{ url('/transactions') . '?digital=' . $id }}" aria-label="Ver lista de transações da conta {{ $account ?: $id }}">
                  <i class="bi bi-card-list"></i> Transações
                </a>
              @endif
            </div>
          </div>
        </div>
      @endforeach
    </div>

    @if(method_exists($accounts, 'links'))
      <div class="mt-3 d-flex justify-content-center">
        {{ $accounts->withQueryString()->onEachSide(1)->links('pagination::bootstrap-5') }}
      </div>
    @endif
  @endif
</div>
@endsection
