@extends('layouts.app')

@section('title', 'Entrar')

@push('styles')
<style>
  :root{
    --ink:#0f172a;
    --muted:#6b7280;
    --surface:#ffffff;
    --surface-2:#f7f8fb;
    --border:#e5e7eb;
    --brand:#0ea5e9;
    --brand-2:#3abff8;
    --focus:rgba(14,165,233,.22);
    --danger:#ef4444;
  }
  [data-theme="dark"]{
    --ink:#e7ebf0;
    --muted:#9aa3ad;
    --surface:#0f172a;
    --surface-2:#0b1220;
    --border:rgba(255,255,255,.10);
    --focus:rgba(59,130,246,.28);
  }

  .auth-wrap{
    min-height: calc(100dvh - 72px); /* 72 ~ altura da navbar */
    display:grid; place-items:center;
    padding: 2rem 1rem;
  }
  .auth-card{
    width:100%; max-width: 440px;
    border:1px solid var(--border);
    background: color-mix(in srgb, var(--surface) 85%, transparent);
    backdrop-filter: saturate(180%) blur(12px);
    border-radius: 16px;
    box-shadow: 0 20px 48px rgba(0,0,0,.12);
    overflow: hidden;
  }
  .auth-card .card-header{
    background: linear-gradient(135deg, var(--brand), var(--brand-2));
    border:0; color:#0b1220;
  }
  .brand-mark{
    font-weight: 900; letter-spacing:.2px;
    -webkit-background-clip:text; background-clip:text;
    color:#0b1220; opacity:.9;
  }
  .form-control, .form-select{
    background: var(--surface); color: var(--ink); border-color: var(--border);
  }
  .form-control:focus{
    border-color: var(--brand); box-shadow: 0 0 0 .2rem var(--focus);
  }
  .input-help{ color: var(--muted); font-size: .9rem; }
  .btn-ghost{ background: var(--surface); border:1px solid var(--border); color: var(--ink); }
  .btn-ghost:hover{ border-color: var(--brand); box-shadow: 0 0 0 3px var(--focus); }
  .divider{
    display:flex;align-items:center;gap:.75rem;color:var(--muted);font-weight:600;
  }
  .divider::before,.divider::after{
    content:"";height:1px;background:var(--border);flex:1;
  }
  .badge-soft{
    border:1px solid var(--border);
    background: var(--surface);
    color: var(--muted); font-weight:700;
    padding:.35rem .6rem; border-radius:999px;
  }
  .list-compact{ margin:0; padding-left: 1rem; color:var(--muted); }
  .list-compact li{ margin:.2rem 0; }
</style>
@endpush

@section('content')
<div class="auth-wrap container">
  <div class="auth-card card">
    <div class="card-header py-3">
      <div class="d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
          <span class="avatar-initials" style="width:42px;height:42px;">G</span>
          <div>
            <div class="brand-mark">{{ config('app.name','GlobalPag') }}</div>
            <div class="small" style="opacity:.85;">Acesse sua conta</div>
          </div>
        </div>
        <button id="themeToggleLogin" class="btn btn-ghost btn-sm" type="button" title="Alternar tema">
          <i class="bi bi-sun-fill d-inline theme-icon-light"></i>
          <i class="bi bi-moon-stars-fill d-none theme-icon-dark"></i>
        </button>
      </div>
    </div>

    <div class="card-body p-4">
      {{-- Alerts globais --}}
      @if (session('status'))
        <div class="alert alert-success app-alert">{{ session('status') }}</div>
      @endif
      @if (session('error'))
        <div class="alert alert-danger app-alert">{{ session('error') }}</div>
      @endif

      <form method="POST" action="{{ route('login') }}" novalidate>
        @csrf

        {{-- Email --}}
        <div class="mb-3">
          <label for="email" class="form-label fw-semibold">
            E-mail
          </label>
          <input
            id="email"
            type="email"
            name="email"
            value="{{ old('email') }}"
            class="form-control @error('email') is-invalid @enderror"
            placeholder="voce@empresa.com"
            required
            autocomplete="email"
            autofocus
          >
          @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
          @else
            <div class="input-help">Use o e-mail cadastrado.</div>
          @enderror
        </div>

        {{-- Senha --}}
        <div class="mb-3">
          <div class="d-flex justify-content-between">
            <label for="password" class="form-label fw-semibold mb-1">Senha</label>
            @if (Route::has('password.request'))
              <a class="small" href="{{ route('password.request') }}">
                Esqueci minha senha
              </a>
            @endif
          </div>
          <div class="input-group">
            <input
              id="password"
              type="password"
              name="password"
              class="form-control @error('password') is-invalid @enderror"
              placeholder="Mínimo 8 caracteres"
              required
              autocomplete="current-password"
            >
            <button class="btn btn-ghost" type="button" id="togglePwd" aria-label="Mostrar/ocultar senha">
              <i class="bi bi-eye"></i>
            </button>
            @error('password')
              <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
          </div>
        </div>

        {{-- Lembrar-me --}}
        <div class="mb-3 form-check">
          <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember', true) ? 'checked' : '' }}>
          <label class="form-check-label" for="remember">Manter conectado</label>
        </div>

        {{-- Placeholder 2FA (opcional) --}}
        @if(config('auth.two_factor_placeholder', false))
        <div class="mb-3">
          <label for="otp" class="form-label fw-semibold">Código 2FA (opcional)</label>
          <input id="otp" name="otp" inputmode="numeric" pattern="[0-9]*" class="form-control" placeholder="123 456">
          <div class="input-help">Se sua conta tiver 2FA, insira o código do app autenticador.</div>
        </div>
        @endif

        {{-- Placeholder reCAPTCHA (opcional) --}}
        @if(config('services.recaptcha.enabled', false))
        <div class="mb-3">
          {{-- Ex.: {!! NoCaptcha::display() !!} --}}
          <span class="badge-soft">reCAPTCHA Área</span>
        </div>
        @endif

        <div class="d-grid">
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-box-arrow-in-right me-1"></i> Entrar
          </button>
        </div>
      </form>

      {{-- Social (exemplo/placeholder) --}}
      @if(config('services.social_login.enabled', false))
      <div class="my-3 divider">ou continue com</div>
      <div class="d-flex gap-2">
        <a href="{{ route('oauth.redirect','google') }}" class="btn btn-ghost w-100">
          <i class="bi bi-google me-1"></i> Google
        </a>
        <a href="{{ route('oauth.redirect','github') }}" class="btn btn-ghost w-100">
          <i class="bi bi-github me-1"></i> GitHub
        </a>
      </div>
      @endif

      {{-- Segurança (tip) --}}
      <div class="mt-4 small">
        <span class="badge-soft"><i class="bi bi-shield-lock me-1"></i> Segurança</span>
        <ul class="list-compact mt-2">
          <li>Conexão protegida por HTTPS.</li>
          <li>Várias tentativas falhas serão temporariamente bloqueadas.</li>
          <li>Ative 2FA para mais segurança.</li>
        </ul>
      </div>

      {{-- Registrar (se existir) --}}
      @if (Route::has('register'))
        <div class="mt-3 text-center">
          <span class="text-muted">Novo por aqui?</span>
          <a href="{{ route('register') }}">Criar conta</a>
        </div>
      @endif
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  // Mostrar/ocultar senha
  (function(){
    const btn = document.getElementById('togglePwd');
    const input = document.getElementById('password');
    if(btn && input){
      btn.addEventListener('click', () => {
        const isPwd = input.type === 'password';
        input.type = isPwd ? 'text' : 'password';
        btn.querySelector('i')?.classList.toggle('bi-eye');
        btn.querySelector('i')?.classList.toggle('bi-eye-slash');
      });
    }
  })();

  // Tema local (botão no header do card)
  (function(){
    const root = document.documentElement;
    const btn = document.getElementById('themeToggleLogin');
    const iconLight = btn?.querySelector('.theme-icon-light');
    const iconDark  = btn?.querySelector('.theme-icon-dark');

    function setTheme(mode){
      root.setAttribute('data-theme', mode);
      localStorage.setItem('app-theme', mode);
      if(mode === 'dark'){ iconLight?.classList.add('d-none'); iconDark?.classList.remove('d-none'); }
      else { iconDark?.classList.add('d-none'); iconLight?.classList.remove('d-none'); }
    }
    const saved = localStorage.getItem('app-theme');
    const prefers = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    setTheme(saved || prefers);

    btn?.addEventListener('click', () => {
      const curr = root.getAttribute('data-theme') || 'light';
      setTheme(curr === 'light' ? 'dark' : 'light');
    });
  })();
</script>
@endpush
