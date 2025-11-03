@extends('layouts.app')

@section('title', 'Selecionar conta')

@push('styles')
<style>
  /* Grid dos cards (tema branco) */
  .acc-grid{
    display:grid; gap:1rem;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  }

  /* Card base */
  .acc-card{
    position:relative;
    border:1px solid var(--border);
    border-radius:16px;
    background:#fff;
    box-shadow: 0 8px 24px rgba(0,0,0,.06);
    transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease;
  }
  .acc-card:hover{
    transform: translateY(-2px);
    box-shadow: 0 14px 34px rgba(0,0,0,.08);
  }

  /* Destaque quando selecionada + selo discreto e elegante */
  .acc-card.selected{
    border-color:#b6e3ff;
    box-shadow: 0 0 0 3px rgba(14,165,233,.14), 0 12px 28px rgba(0,0,0,.06);
  }
  .acc-flag{
    position:absolute; top:12px; right:12px;
    display:inline-flex; align-items:center; gap:.35rem;
    padding:.35rem .6rem; border-radius:999px;
    background:#e6f4ff; border:1px solid #b6e3ff; color:#0b3a67;
    font-weight:700; font-size:.78rem;
  }

  /* Cabeçalho minimalista */
  .acc-header{
    display:flex; align-items:start; justify-content:space-between;
    gap:.9rem; padding:1rem 1rem .5rem 1rem;
    border-bottom:1px dashed var(--border);
  }
  .acc-title{ font-weight:800; line-height:1.15; color:#0f172a; }
  .acc-sub{ color:#6c757d; font-size:.85rem; }

  /* Chips de meta com excelente contraste no claro */
  .acc-meta{ display:flex; gap:.5rem; flex-wrap:wrap; padding:.75rem 1rem; }
  .chip{
    display:inline-flex; align-items:center; gap:.5rem;
    padding:.45rem .65rem; border-radius:10px;
    background:#ffffff;
    border:1px solid #e5e7eb;
    color:#0f172a;
    font-weight:600; font-size:.95rem;
  }
  .chip .bi{ opacity:.7; }

  /* Saldo */
  .acc-balance-wrap{ padding:.25rem 1rem .9rem 1rem; }
  .acc-balance-label{ color:#6c757d; font-size:.8rem; margin-bottom:.1rem; }
  .acc-balance{
    font-size:1.45rem; font-weight:800; letter-spacing:.01em; color:#0f172a;
  }
  .acc-balance small{ color:#64748b; font-weight:600; }

  /* Rodapé */
  .acc-footer{
    display:flex; flex-wrap:wrap; gap:.5rem;
    padding:1rem; border-top:1px dashed var(--border);
  }

  /* Botões */
  .btn-ghost{
    background:#fff; border:1px solid var(--border); color:#0f172a;
  }
  .btn-ghost:hover{
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(14,165,233,.15);
  }
  .btn-primary-strong{
    background: linear-gradient(135deg, var(--primary), #3abff8);
    color:#0b1220; font-weight:800; border:none;
  }
  .btn-primary-strong:hover{ filter:brightness(1.06); }

  /* Pílula no topo da página para conta ativa */
  .top-selected{
    background:#e6f4ff; border:1px solid #b6e3ff; color:#0b3a67;
    font-weight:700;
  }
</style>
@endpush

@section('content')
<div class="container-xl">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <div>
      <h1 class="h3 mb-1">Selecionar conta</h1>
      <div class="text-secondary">Escolha a conta digital para usar no painel</div>
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

  <form method="GET" action="{{ route('select-account') }}" class="row g-2 mb-4">
    <div class="col-md-7">
      <div class="input-group">
        <span class="input-group-text bg-white border-end-0">
          <i class="bi bi-search"></i>
        </span>
        <input type="text" class="form-control border-start-0" name="q" value="{{ $q }}"
               placeholder="Buscar por conta, agência, empresa..." autocomplete="off">
      </div>
    </div>
    <div class="col-md-2">
      <select name="limit" class="form-select">
        @foreach ([10,25,50,100] as $opt)
          <option value="{{ $opt }}" @selected(($limit ?? 25)==$opt)>{{ $opt }} / pág.</option>
        @endforeach
      </select>
    </div>
    <div class="col-md-3 d-grid d-md-flex gap-2">
      <button class="btn btn-primary">
        <i class="bi bi-funnel me-1"></i> Filtrar
      </button>
      <a href="{{ route('select-account') }}" class="btn btn-outline-secondary">
        <i class="bi bi-x-lg me-1"></i> Limpar
      </a>
    </div>
  </form>

  @php $hasAcc = is_iterable($accounts ?? []) && count($accounts) > 0; @endphp

  @if (!$hasAcc)
    <div class="card card-md shadow-sm">
      <div class="card-body text-center">
        <div class="h2 mb-1">Nenhuma conta encontrada</div>
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
          $isSelected = session('digital_account_id') == $id;
        @endphp

        <div class="acc-card {{ $isSelected ? 'selected' : '' }}">
          @if($isSelected)
            <span class="acc-flag">
              <i class="bi bi-check2-circle"></i> Selecionada
            </span>
          @endif

          <div class="acc-header">
            <div class="flex-grow-1">
              <div class="acc-title text-truncate" title="{{ $company }}">{{ $company ?: '—' }}</div>
              <div class="acc-sub">ID #{{ $id }}</div>
            </div>
          </div>

          <div class="acc-meta">
            <span class="chip" title="Agência">
              <i class="bi bi-building"></i> Ag. <strong>{{ $agency ?: '—' }}</strong>
            </span>
            <span class="chip" title="Conta">
              <i class="bi bi-credit-card-2-front"></i> Conta <strong>{{ $account ?: '—' }}</strong>
            </span>
          </div>

          <div class="acc-balance-wrap">
            <div class="acc-balance-label">Saldo</div>
            <div class="acc-balance">
              @if(!is_null($balance))
                R$ {{ number_format((float)$balance, 2, ',', '.') }}
                @if($dtBal)
                  <small> · {{ \Illuminate\Support\Carbon::parse($dtBal)->tz('America/Sao_Paulo')->format('d/m H:i') }}</small>
                @endif
              @else
                —
              @endif
            </div>
          </div>

          <div class="acc-footer">
            <form method="POST" action="{{ route('select-account.store') }}">
              @csrf
              <input type="hidden" name="digital_account_id" value="{{ $id }}">
              <input type="hidden" name="agency" value="{{ $agency }}">
              <input type="hidden" name="account" value="{{ $account }}">
              <button type="submit" class="btn btn-primary-strong btn-sm">
                <i class="bi bi-check2-circle me-1"></i> Usar esta conta
              </button>
            </form>
            <a class="btn btn-ghost btn-sm"
               href="{{ route('reports.daily-transactions', ['digital' => $id]) }}">
              <i class="bi bi-graph-up-arrow me-1"></i> Ver Daily
            </a>
          </div>
        </div>
      @endforeach
    </div>

    {{-- Paginação (se $accounts for Paginator/LengthAwarePaginator) --}}
    @if(method_exists($accounts, 'links'))
      <div class="mt-3 d-flex justify-content-center">
        {{ $accounts->withQueryString()->onEachSide(1)->links('pagination::bootstrap-5') }}
      </div>
    @endif
  @endif
</div>
@endsection
