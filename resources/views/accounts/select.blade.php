@extends('layouts.app')

@section('title', 'Selecionar conta')

@push('styles')
<style>
  :root{
    --border: var(--tblr-border-color, #e5e7eb);
    --primary: var(--tblr-primary, #0ea5e9);
    --text: #0f172a;
    --muted: #6c757d;
    --bg: #fff;
  }

  /* ====== Layout base ====== */
  .acc-grid{
    display:grid;
    gap:clamp(.75rem, 2vw, 1rem);
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
  }
  @media (min-width: 600px){
    .acc-grid{ grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); }
  }

  .acc-card{
    position:relative; overflow:hidden;
    border:1px solid var(--border); border-radius:16px; background:var(--bg);
    box-shadow: 0 4px 14px rgba(0,0,0,.06);
    transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease;
  }
  .acc-card:hover{ transform: translateY(-2px); box-shadow: 0 10px 26px rgba(0,0,0,.10); }
  .acc-card.selected{ border-color:#b6e3ff; box-shadow: 0 0 0 3px rgba(14,165,233,.14), 0 10px 26px rgba(0,0,0,.06); }
  @media (prefers-reduced-motion: reduce){ .acc-card, .acc-card:hover{ transition:none; transform:none; } }

  .acc-flag{
    position:absolute; top:10px; right:10px; z-index:2;
    display:inline-flex; align-items:center; gap:.35rem;
    padding:.35rem .6rem; border-radius:999px;
    background:#e6f4ff; border:1px solid #b6e3ff; color:#0b3a67;
    font-weight:700; font-size:.78rem;
  }

  /* ====== Cabeçalho ====== */
  .acc-header{
    display:flex; align-items:flex-start; justify-content:space-between; gap:.75rem;
    padding:clamp(.9rem, 2.8vw, 1.1rem) clamp(1rem, 3.2vw, 1.25rem) .6rem;
    border-bottom:1px dashed var(--border);
  }
  .acc-title{
    font-weight:900; line-height:1.15; color:var(--text);
    font-size: clamp(1.05rem, 3.2vw, 1.15rem);
  }
  .acc-sub{ color:var(--muted); font-size: clamp(.78rem, 2.6vw, .86rem); }

  /* ====== Destaque do mapeamento (nome/label da conta) ====== */
  .acc-map{
    margin:.4rem 0 0; display:flex; flex-wrap:wrap; gap:.4rem .6rem; align-items:center;
  }
  .map-badge{
    display:inline-flex; align-items:center; gap:.5rem;
    font-weight:800; letter-spacing:.01em; color:#0b3a67;
    background:#e6f4ff; border:1px solid #b6e3ff; border-radius:999px;
    padding:.38rem .65rem; font-size: clamp(.82rem, 2.4vw, .9rem);
  }
  .map-badge .label-acc{ font-weight:900; }
  .map-badge .num-acc{ font-weight:800; opacity:.95; }

  /* ====== acc-meta melhorado ======
     - mobile: bloco (label em cima, valor embaixo)
     - >=768px: linha (ícone + label à esquerda, valor à direita)
  */
  .acc-meta{
    padding: .85rem clamp(1rem, 3.2vw, 1.25rem);
    display: grid;
    gap: .65rem;
    grid-template-columns: 1fr;
  }
  @media (min-width: 768px){
    .acc-meta{
      grid-template-columns: repeat(3, 1fr);
    }
  }

  .kv{
    display:grid;
    grid-template-columns: 1fr; /* mobile: empilhado */
    gap:.25rem;
    padding:.6rem .7rem;
    border:1px solid #e5e7eb; border-radius:10px; background:#fff;
  }
  @media (min-width: 768px){
    .kv{
      grid-template-columns: auto 1fr; /* desktop: lado a lado */
      align-items:center;
      column-gap:.6rem;
    }
  }
  .kv-icon{
    display:inline-flex; align-items:center; justify-content:center;
    width:28px; height:28px; border-radius:8px; border:1px solid #eaecef; background:#fff;
  }
  .kv-icon i{ opacity:.8; font-size:1rem; }

  .kv-key{
    color:var(--muted);
    font-size: clamp(.78rem, 2.2vw, .84rem);
    line-height:1.1;
  }
  .kv-val{
    color:var(--text);
    font-weight:800;
    font-size: clamp(.95rem, 2.6vw, 1rem);
    overflow:hidden; text-overflow:ellipsis; white-space:nowrap;
  }

  /* ====== Saldo ====== */
  .acc-balance-wrap{ padding:.25rem clamp(1rem, 3.2vw, 1.25rem) .95rem; }
  .acc-balance-label{ color:var(--muted); font-size: clamp(.75rem, 2.4vw, .8rem); margin-bottom:.15rem; }
  .acc-balance{
    font-size: clamp(1.25rem, 5vw, 1.55rem);
    font-weight:900; letter-spacing:.01em; color:var(--text);
    display:flex; align-items:baseline; gap:.35rem; flex-wrap:wrap;
  }
  .acc-balance small{ color:#64748b; font-weight:600; font-size: clamp(.72rem, 2.2vw, .82rem); }

  /* ====== Rodapé / Ações ====== */
  .acc-footer{
    display:flex; gap:.5rem; padding:clamp(.85rem, 2.5vw, 1.1rem);
    border-top:1px dashed var(--border); flex-wrap:wrap;
  }
  .btn-ghost{
    background:#fff; border:1px solid var(--border); color:var(--text); min-height:44px;
  }
  .btn-ghost:hover{ border-color: var(--primary); box-shadow: 0 0 0 3px rgba(14,165,233,.15); }
  .btn-primary-strong{
    background: linear-gradient(135deg, var(--primary), #3abff8);
    color:#0b1220; font-weight:900; border:none; min-height:44px;
  }
  .btn-primary-strong:hover{ filter:brightness(1.06); }

  /* ====== Mobile-first ====== */
  @media (max-width: 576px){
    .acc-footer{ flex-direction:column; }
    .acc-footer form, .acc-footer a{ width:100%; }
    .acc-title{ max-width: 92vw; }
  }

  /* ====== Pílula topo ====== */
  .top-selected{
    background:#e6f4ff; border:1px solid #b6e3ff; color:#0b3a67; font-weight:700;
  }

  /* ====== Filtro agora NÃO é sticky (apenas cartão visual) ====== */
  .filter-sticky{
    background:#fff;
    padding:.25rem .25rem .5rem;
    border-radius:12px;
    box-shadow: 0 4px 14px rgba(0,0,0,.06);
    /* sem position: sticky */
  }
</style>
@endpush

@section('content')
@php use App\Support\AccountLabels; @endphp

<div class="container-xl">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <div>
      <h1 class="h3 mb-1" style="font-size:clamp(1.1rem,4.5vw,1.35rem);">Selecionar conta</h1>
      <div class="text-secondary" style="font-size:clamp(.85rem,3vw,1rem);">Escolha a conta digital para usar no painel</div>
    </div>
    @php $selected = session('digital_account_id'); @endphp
    @if ($selected)
      <span class="badge rounded-pill top-selected px-3 py-2">
        <i class="bi bi-check2-circle me-1"></i> Conta ativa #{{ $selected }}
      </span>
    @endif
  </div>

  @if (session('error'))
    <div class="alert alert-danger mb-3" role="alert">{{ session('error') }}</div>
  @endif
  @if ($pendingMsg ?? false)
    <div class="alert alert-warning mb-3" role="alert">{{ $pendingMsg }}</div>
  @endif

  <div class="filter-sticky mb-3">
    <form method="GET" action="{{ route('select-account') }}" class="row g-2">
      <div class="col-12 col-md-7">
        <div class="input-group">
          <span class="input-group-text bg-white border-end-0">
            <i class="bi bi-search"></i>
          </span>
          <input type="text"
                 class="form-control border-start-0"
                 name="q"
                 value="{{ $q }}"
                 placeholder="Buscar por conta, agência, empresa ou rótulo..."
                 inputmode="search"
                 autocomplete="off"
                 aria-label="Buscar conta">
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
          <i class="bi bi-funnel me-1"></i> Filtrar
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
        <div class="h2 mb-1" style="font-size:clamp(1.1rem,4.5vw,1.4rem);">Nenhuma conta encontrada</div>
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
        @endphp

        <div class="acc-card {{ $isSelected ? 'selected' : '' }}">
          @if($isSelected)
            <span class="acc-flag"><i class="bi bi-check2-circle"></i> Selecionada</span>
          @endif

          <div class="acc-header">
            <div class="flex-grow-1">
              <div class="acc-title text-truncate" title="{{ $company }}">{{ $company ?: '—' }}</div>
              <div class="acc-sub">ID #{{ $id }}</div>

              @if($account)
                <div class="acc-map" aria-label="Identificador mapeado">
                  <span class="map-badge" title="Nome mapeado da conta">
                    <i class="bi bi-tag"></i>
                    <span class="label-acc">{{ $label }}</span>
                    <span class="num-acc">· {{ $account }}</span>
                  </span>
                </div>
              @endif
            </div>
          </div>

          {{-- acc-meta (grid responsivo) --}}
          <div class="acc-meta">
            <div class="kv" title="Agência">
              <span class="kv-icon"><i class="bi bi-building"></i></span>
              <div class="kv-key">Agência</div>
              <div class="kv-val">{{ $agency ?: '—' }}</div>
            </div>
            <div class="kv" title="Conta">
              <span class="kv-icon"><i class="bi bi-credit-card-2-front"></i></span>
              <div class="kv-key">Conta</div>
              <div class="kv-val">{{ $account ?: '—' }}</div>
            </div>
            <div class="kv" title="Empresa">
              <span class="kv-icon"><i class="bi bi-briefcase"></i></span>
              <div class="kv-key">Empresa</div>
              <div class="kv-val" title="{{ $company }}">{{ $company ?: '—' }}</div>
            </div>
          </div>

          <div class="acc-balance-wrap">
            <div class="acc-balance-label">Saldo</div>
            <div class="acc-balance">
              @if(!is_null($balance))
                R$ {{ number_format((float)$balance, 2, ',', '.') }}
                @if($dtBal)
                  <small>· {{ \Illuminate\Support\Carbon::parse($dtBal)->tz('America/Sao_Paulo')->format('d/m H:i') }}</small>
                @endif
              @else
                —
              @endif
            </div>
          </div>

          <div class="acc-footer">
            <form method="POST" action="{{ route('select-account.store') }}" class="flex-grow-1">
              @csrf
              <input type="hidden" name="digital_account_id" value="{{ $id }}">
              <input type="hidden" name="agency" value="{{ $agency }}">
              <input type="hidden" name="account" value="{{ $account }}">
              <button type="submit" class="btn btn-primary-strong btn-sm w-100" aria-label="Usar conta {{ $account ?: $id }}">
                <i class="bi bi-check2-circle me-1"></i> Usar esta conta
              </button>
            </form>
            <a class="btn btn-ghost btn-sm" href="{{ route('reports.daily-transactions', ['digital' => $id]) }}" aria-label="Ver Daily da conta {{ $account ?: $id }}">
              <i class="bi bi-graph-up-arrow me-1"></i> Ver Daily
            </a>
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
