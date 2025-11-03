<!doctype html>
<html lang="pt-BR" data-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">

  <title>
    @hasSection('title')@yield('title') · @endif
    {{ config('app.name', 'Portal') }}
  </title>

  {{-- Bootstrap core --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  {{-- Bootstrap Icons --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  @stack('head')
  @stack('styles')

  <style>
    :root{
      --brand:#0ea5e9;
      --brand-2:#3abff8;
      --ink:#0f172a;
      --ink-weak:#334155;
      --muted:#6c757d;
      --surface:#ffffff;
      --surface-2:#f7f8fb;
      --border:#e9ecef;
      --nav-bg:rgba(255,255,255,.85);
      --nav-border:rgba(0,0,0,.06);
      --nav-ink:#0b1220;           /* cor dos links no navbar (claro) */
      --focus:rgba(14,165,233,.22);
    }
    [data-theme="dark"]{
      --ink:#e7ebf0;
      --ink-weak:#c6d0da;
      --muted:#9aa3ad;
      --surface:#0f172a;
      --surface-2:#0b1220;
      --border:rgba(255,255,255,.12);
      --nav-bg:rgba(10,16,28,.92);  /* mais opaco p/ legibilidade */
      --nav-border:rgba(255,255,255,.16);
      --nav-ink:#f2f5f9;            /* links claros no navbar (escuro) */
      --focus:rgba(59,130,246,.32);
    }

    html, body { background: var(--surface-2); color: var(--ink); }
    .container { max-width: 1200px; }

    /* Navbar “glass” fixa */
    .app-navbar{
      position: sticky; top: 0; z-index: 1030;
      backdrop-filter: saturate(180%) blur(14px);
      background: var(--nav-bg);
      color: var(--nav-ink);
      border-bottom: 1px solid var(--nav-border);
      transition: box-shadow .15s ease, border-color .15s ease, background .15s ease;
    }
    .app-navbar.scrolled{ box-shadow: 0 6px 24px rgba(0,0,0,.12); }

    .navbar-brand{
      font-weight: 800; letter-spacing: .2px;
      background: linear-gradient(90deg, var(--brand), var(--brand-2));
      -webkit-background-clip: text; background-clip: text; color: transparent !important;
    }

    .nav-link{
      color: var(--nav-ink) !important;
      opacity: 1;                     /* sempre 100% p/ máxima leitura */
      font-weight: 600;
    }
    .nav-link:hover, .nav-link:focus{
      opacity: 1;
      text-decoration: none;
    }
    .nav-link.active{ position: relative; }
    .nav-link.active::after{
      content: ""; position: absolute; left: .5rem; right: .5rem; bottom: -6px;
      height: 3px; border-radius: 999px;
      background: linear-gradient(90deg, var(--brand), var(--brand-2));
    }
    .nav-link:focus-visible{
      outline: none;
      box-shadow: 0 0 0 .2rem var(--focus);
      border-radius: .35rem;
    }

    /* Toggler visível no dark */
    .navbar-toggler{
      border-color: var(--border);
      color: var(--nav-ink);
    }
    .navbar-toggler:focus{ box-shadow: 0 0 0 .2rem var(--focus); }
    .navbar-toggler-icon{
      background-image:
        url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='currentColor' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
      filter: none; /* garante contraste no dark */
    }

    /* Pílula: conta ativa */
    .context-pill{
      display:inline-flex; align-items:center; gap:.4rem;
      padding:.35rem .6rem; border-radius:999px;
      background:var(--surface); border:1px solid var(--border); color:var(--ink);
      font-weight:700; font-size:.9rem;
    }

    /* Avatar */
    .avatar-initials{
      width: 30px; height: 30px; border-radius: 999px; display: grid; place-items: center;
      font-weight: 800; font-size: .85rem; color: #0b1220;
      background: linear-gradient(135deg, var(--brand), var(--brand-2));
    }
    [data-theme="dark"] .avatar-initials{ color: #0b1220; }

    /* Dropdowns e itens (contraste no dark) */
    .dropdown-menu{ background: var(--surface); border-color: var(--border); }
    .dropdown-item{ color: var(--ink); }
    .dropdown-item:hover, .dropdown-item:focus{
      background: rgba(14,165,233,.12);
      color: var(--ink);
    }
    .dropdown-divider{ border-color: var(--border); }

    /* Form controls no tema dinâmico */
    .form-control, .form-select{ background: var(--surface); color: var(--ink); border-color: var(--border); }
    .form-control::placeholder{ color: var(--ink-weak); }
    .form-control:focus, .form-select:focus{
      border-color: var(--brand); box-shadow: 0 0 0 .2rem var(--focus);
      color: var(--ink); background: var(--surface);
    }

    /* Cards e tabelas */
    .card, .card-plain{ background: var(--surface); border-color: var(--border); color: var(--ink); }
    .card-header, .card-footer{ background: var(--surface); border-color: var(--border); }
    .table{ color: var(--ink); }
    .table thead th{ color: var(--ink-weak); background: var(--surface); border-bottom-color: var(--border); }
    .table td, .table th{ border-color: var(--border); }
    .table-hover tbody tr:hover{ background: rgba(14,165,233,.06); }

    /* Botões “ghost” */
    .btn-ghost{ background: var(--surface); border:1px solid var(--border); color: var(--ink); }
    .btn-ghost:hover{ border-color: var(--brand); box-shadow: 0 0 0 3px var(--focus); }

    /* Corrige setas gigantes de paginação injetadas por temas */
    a[rel="prev"]::before, a[rel="next"]::after, a[rel="prev"]::after, a[rel="next"]::before{
      content: none !important; display: none !important;
    }
    .pagination a[rel="prev"], .pagination a[rel="next"]{
      position: static !important; font-size: inherit !important; line-height: inherit !important;
    }
    .pagination .page-link svg{ width:16px !important; height:16px !important; vertical-align:-2px; }
  </style>
</head>
<body>
  {{-- NAVBAR (sem busca; contraste corrigido no dark) --}}
  <nav class="navbar navbar-expand-lg app-navbar">
    <div class="container-fluid px-3">
      @if(Route::has('dashboard'))
        <a class="navbar-brand" href="{{ route('dashboard') }}">
          {{ config('app.name', 'GlobalPag') }}
        </a>
      @else
        <span class="navbar-brand">{{ config('app.name', 'GlobalPag') }}</span>
      @endif

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#appNav"
              aria-controls="appNav" aria-expanded="false" aria-label="Alternar navegação">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div id="appNav" class="collapse navbar-collapse">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          @if(Route::has('dashboard'))
            <li class="nav-item">
              <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                <i class="bi bi-speedometer2 me-1"></i> Dashboard
              </a>
            </li>
          @endif

          @if(Route::has('select-account'))
            <li class="nav-item">
              <a class="nav-link {{ request()->routeIs('select-account') ? 'active' : '' }}" href="{{ route('select-account') }}">
                <i class="bi bi-credit-card-2-front me-1"></i> Contas
              </a>
            </li>
          @endif

          {{-- Slot opcional para páginas adicionarem itens no menu --}}
          @stack('nav.items')
        </ul>

        {{-- Ícone de notificações (slot opcional) --}}
        @stack('nav.actions.left')

        {{-- Conta ativa (se houver) --}}
        @php $activeDigital = session('digital_account_id'); @endphp
        @if($activeDigital)
          <span class="context-pill me-2 d-none d-md-inline-flex">
            <i class="bi bi-check2-circle"></i> Conta #{{ $activeDigital }}
          </span>
        @endif

        <div class="d-flex align-items-center gap-2">
          {{-- Tema --}}
          <button class="btn btn-ghost btn-sm" id="themeToggle" type="button" aria-label="Alternar tema" title="Alternar tema">
            <i class="bi bi-sun-fill d-inline theme-icon-light"></i>
            <i class="bi bi-moon-stars-fill d-none theme-icon-dark"></i>
          </button>

          {{-- Notificações (exemplo; substitua por sua rota) --}}
          @if(Route::has('notifications'))
            <a class="btn btn-ghost btn-sm position-relative" href="{{ route('notifications') }}" title="Notificações">
              <i class="bi bi-bell"></i>
              {{-- <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">3</span> --}}
            </a>
          @endif

          {{-- Usuário --}}
          @auth
            <div class="dropdown">
              <button class="btn btn-ghost btn-sm d-flex align-items-center gap-2" data-bs-toggle="dropdown">
                <span class="avatar-initials">
                  @php
                    $nm = auth()->user()->name ?? 'U';
                    echo mb_strtoupper(mb_substr(trim($nm),0,1));
                  @endphp
                </span>
                <span class="d-none d-sm-inline">{{ \Illuminate\Support\Str::limit($nm, 18) }}</span>
              </button>
              <div class="dropdown-menu dropdown-menu-end shadow-sm">
                @if(Route::has('profile'))
                  <a class="dropdown-item" href="{{ route('profile') }}"><i class="bi bi-person me-2"></i> Perfil</a>
                @endif
                @if(Route::has('settings'))
                  <a class="dropdown-item" href="{{ route('settings') }}"><i class="bi bi-gear me-2"></i> Configurações</a>
                @endif

                @if(Route::has('profile') || Route::has('settings'))
                  <div class="dropdown-divider"></div>
                @endif

                @if(Route::has('logout'))
                  <form method="POST" action="{{ route('logout') }}" class="px-2">
                    @csrf
                    <button class="btn btn-danger w-100"><i class="bi bi-box-arrow-right me-1"></i> Sair</button>
                  </form>
                @else
                  <div class="px-3 py-2 text-muted small">Olá, {{ $nm }}</div>
                @endif
              </div>
            </div>
          @else
            @if(Route::has('login'))
              <a class="btn btn-primary btn-sm" href="{{ route('login') }}">Entrar</a>
            @endif
          @endauth

          {{-- Ações extras à direita (slot) --}}
          @stack('nav.actions.right')
        </div>
      </div>
    </div>
  </nav>

  {{-- FLASHES + CONTEÚDO --}}
  <div class="container my-3">
    @if (session('status'))
      <div class="alert alert-success app-alert">{{ session('status') }}</div>
    @endif

    @if (session('error'))
      <div class="alert alert-danger app-alert">{{ session('error') }}</div>
    @endif

    @if ($errors->any())
      <div class="alert alert-danger app-alert">
        <ul class="mb-0">
          @foreach ($errors->all() as $err)
            <li>{{ $err }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    @yield('content')
  </div>

  @stack('scripts')
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Tooltips
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));

    // Sombra na navbar ao rolar
    const navbar = document.querySelector('.app-navbar');
    const onScroll = () => {
      if (window.scrollY > 6) navbar.classList.add('scrolled'); else navbar.classList.remove('scrolled');
    };
    document.addEventListener('scroll', onScroll); onScroll();

    // Tema claro/escuro com persistência
    const root = document.documentElement;
    const themeToggle = document.getElementById('themeToggle');
    const iconLight = themeToggle?.querySelector('.theme-icon-light');
    const iconDark  = themeToggle?.querySelector('.theme-icon-dark');

    function setTheme(mode){
      root.setAttribute('data-theme', mode);
      localStorage.setItem('app-theme', mode);
      if(mode === 'dark'){ iconLight?.classList.add('d-none'); iconDark?.classList.remove('d-none'); }
      else { iconDark?.classList.add('d-none'); iconLight?.classList.remove('d-none'); }
    }
    (function initTheme(){
      const saved = localStorage.getItem('app-theme');
      const prefers = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
      setTheme(saved || prefers);
    })();
    themeToggle?.addEventListener('click', () => {
      const curr = root.getAttribute('data-theme') || 'light';
      setTheme(curr === 'light' ? 'dark' : 'light');
    });
  </script>
</body>
</html>
