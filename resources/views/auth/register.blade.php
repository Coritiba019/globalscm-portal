{{-- resources/views/auth/register.blade.php --}}
@extends('layouts.app')

@section('title', 'Criar conta')

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
    --success:#16a34a;
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
    min-height: calc(100dvh - 72px);
    display:grid; place-items:center;
    padding: 2rem 1rem;
  }
  .auth-card{
    width:100%; max-width: 520px;
    border:1px solid var(--border);
    background: color-mix(in srgb, var(--surface) 90%, transparent);
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
    color:#0b1220; opacity:.9;
  }

  .form-control, .form-select{
    background: var(--surface); color: var(--ink); border-color: var(--border);
  }
  .form-control::placeholder{ color:#94a3b8; }
  .form-control:focus{
    border-color: var(--brand); box-shadow: 0 0 0 .2rem var(--focus);
  }
  .input-help{ color: var(--muted); font-size: .9rem; }
  .btn-ghost{ background: var(--surface); border:1px solid var(--border); color: var(--ink); }
  .btn-ghost:hover{ border-color: var(--brand); box-shadow: 0 0 0 3px var(--focus); }

  .pwd-meter{ height: 8px; border-radius: 999px; background: #e5e7eb; overflow: hidden; }
  [data-theme="dark"] .pwd-meter{ background: rgba(255,255,255,.14); }
  .pwd-meter > i{ display:block; height:100%; width:0%; transition: width .25s ease; background: linear-gradient(90deg,#ef4444,#f59e0b,#16a34a); }

  .match-note{ font-size:.9rem; }
  .match-ok{ color: var(--success); }
  .match-bad{ color: var(--danger); }

  .divider{
    display:flex;align-items:center;gap:.75rem;color:var(--muted);font-weight:600;
  }
  .divider::before,.divider::after{ content:"";height:1px;background:var(--border);flex:1; }
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
            <div class="small" style="opacity:.85;">Crie sua conta</div>
          </div>
        </div>
        <button id="themeToggleRegister" class="btn btn-ghost btn-sm" type="button" title="Alternar tema">
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

      <form method="POST" action="{{ route('register') }}" id="registerForm" novalidate>
        @csrf

        {{-- Nome --}}
        <div class="mb-3">
          <label for="name" class="form-label fw-semibold">Nome completo</label>
          <input
            id="name" name="name" type="text"
            class="form-control @error('name') is-invalid @enderror"
            value="{{ old('name') }}" required autocomplete="name" placeholder="Seu nome"
          >
          @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
          @else
            <div class="input-help">Como aparecerá no sistema.</div>
          @enderror
        </div>

        {{-- E-mail --}}
        <div class="mb-3">
          <label for="email" class="form-label fw-semibold">E-mail</label>
          <input
            id="email" name="email" type="email"
            class="form-control @error('email') is-invalid @enderror"
            value="{{ old('email') }}" required autocomplete="username" placeholder="voce@empresa.com"
          >
          @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
          @else
            <div class="input-help">Usado para login e comunicações.</div>
          @enderror
        </div>

        {{-- Senha --}}
        <div class="mb-3">
          <div class="d-flex justify-content-between align-items-end">
            <label for="password" class="form-label fw-semibold mb-1">Senha</label>
            <small class="text-muted">mín. 8 caracteres</small>
          </div>
          <div class="input-group">
            <input
              id="password" name="password" type="password"
              class="form-control @error('password') is-invalid @enderror"
              required autocomplete="new-password" placeholder="Crie uma senha forte"
            >
            <button class="btn btn-ghost" type="button" id="togglePwd" aria-label="Mostrar/ocultar senha">
              <i class="bi bi-eye"></i>
            </button>
          </div>
          @error('password')
            <div class="invalid-feedback d-block">{{ $message }}</div>
          @enderror

          <div class="pwd-meter mt-2" aria-hidden="true"><i id="pwdMeterBar"></i></div>
          <div id="pwdHints" class="input-help mt-1">
            Use letras maiúsculas e minúsculas, números e símbolos.
          </div>
        </div>

        {{-- Confirmar senha --}}
        <div class="mb-3">
          <label for="password_confirmation" class="form-label fw-semibold">Confirmar senha</label>
          <div class="input-group">
            <input
              id="password_confirmation" name="password_confirmation" type="password"
              class="form-control @error('password_confirmation') is-invalid @enderror"
              required autocomplete="new-password" placeholder="Repita a senha"
            >
            <button class="btn btn-ghost" type="button" id="togglePwd2" aria-label="Mostrar/ocultar confirmação">
              <i class="bi bi-eye"></i>
            </button>
          </div>
          @error('password_confirmation')
            <div class="invalid-feedback d-block">{{ $message }}</div>
          @enderror
          <div id="matchNote" class="match-note mt-1" aria-live="polite"></div>
        </div>

        {{-- Termos (opcional) --}}
        @if(config('app.show_terms_on_register', false))
        <div class="mb-3 form-check">
          <input class="form-check-input" type="checkbox" name="terms" id="terms" {{ old('terms') ? 'checked' : '' }}>
          <label class="form-check-label" for="terms">
            Concordo com os <a href="{{ url('/terms') }}" target="_blank" rel="noopener">Termos de Uso</a> e a
            <a href="{{ url('/privacy') }}" target="_blank" rel="noopener">Política de Privacidade</a>.
          </label>
        </div>
        @endif

        <div class="d-flex align-items-center justify-content-between mt-3">
          @if (Route::has('login'))
            <a class="text-muted" href="{{ route('login') }}">Já possui conta? Entrar</a>
          @else
            <span></span>
          @endif

          <button type="submit" class="btn btn-primary" id="submitBtn">
            <span class="btn-text">Registrar</span>
            <span class="spinner-border spinner-border-sm ms-2 d-none" role="status" aria-hidden="true"></span>
          </button>
        </div>
      </form>

      <div class="mt-4 divider">dicas</div>
      <ul class="small text-muted mb-0 ps-3">
        <li>Evite senhas comuns (123456, qwerty, admin, etc.).</li>
        <li>Prefira frases longas com variações (ex.: “Tigre#Corre_2025!”).</li>
        <li>Não reutilize sua senha de outros serviços.</li>
      </ul>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  // Mostrar/ocultar senha
  (function(){
    function toggle(btnId, inputId){
      const btn = document.getElementById(btnId);
      const input = document.getElementById(inputId);
      if(!btn || !input) return;
      btn.addEventListener('click', () => {
        const isPwd = input.type === 'password';
        input.type = isPwd ? 'text' : 'password';
        const icon = btn.querySelector('i');
        icon?.classList.toggle('bi-eye');
        icon?.classList.toggle('bi-eye-slash');
      });
    }
    toggle('togglePwd','password');
    toggle('togglePwd2','password_confirmation');
  })();

  // Medidor de força de senha
  (function(){
    const pwd = document.getElementById('password');
    const meter = document.getElementById('pwdMeterBar');
    const hints = document.getElementById('pwdHints');
    if(!pwd || !meter) return;

    function strengthScore(s){
      let score = 0;
      if(!s) return 0;
      if(s.length >= 8) score += 1;
      if(/[A-Z]/.test(s)) score += 1;
      if(/[a-z]/.test(s)) score += 1;
      if(/[0-9]/.test(s)) score += 1;
      if(/[^A-Za-z0-9]/.test(s)) score += 1;
      if(s.length >= 12) score += 1;
      return Math.min(score, 6);
    }

    function update(){
      const s = pwd.value || '';
      const sc = strengthScore(s);
      const pct = [0, 20, 40, 60, 80, 90, 100][sc] || 0;
      meter.style.width = pct + '%';
      if(hints){
        const labels = ['Muito fraca','Fraca','Regular','OK','Boa','Forte','Excelente'];
        hints.textContent = s ? `Força da senha: ${labels[sc]}` : 'Use letras maiúsculas e minúsculas, números e símbolos.';
      }
      validateForm();
    }
    pwd.addEventListener('input', update);
    update();
  })();

  // Match visual da confirmação
  (function(){
    const pwd = document.getElementById('password');
    const rep = document.getElementById('password_confirmation');
    const note = document.getElementById('matchNote');
    function check(){
      const a = pwd?.value || '', b = rep?.value || '';
      if(!a && !b){ if(note) note.textContent = ''; validateForm(); return; }
      if(note){
        if(a === b){ note.textContent = 'As senhas conferem.'; note.classList.add('match-ok'); note.classList.remove('match-bad'); }
        else { note.textContent = 'As senhas não conferem.'; note.classList.add('match-bad'); note.classList.remove('match-ok'); }
      }
      validateForm();
    }
    pwd?.addEventListener('input', check);
    rep?.addEventListener('input', check);
    check();
  })();

  // Habilitar submit só quando os campos estiverem válidos
  const form = document.getElementById('registerForm');
  const submitBtn = document.getElementById('submitBtn');
  const spinner = submitBtn?.querySelector('.spinner-border');

  function validateForm(){
    if(!form || !submitBtn) return;
    const nameOk = (document.getElementById('name')?.value || '').trim().length >= 2;
    const emailOk = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test((document.getElementById('email')?.value || '').trim());
    const pwd = document.getElementById('password')?.value || '';
    const rep = document.getElementById('password_confirmation')?.value || '';
    const strong = pwd.length >= 8 && /[A-Z]/.test(pwd) && /[a-z]/.test(pwd) && /[0-9]/.test(pwd) && /[^A-Za-z0-9]/.test(pwd);
    const match = !!pwd && pwd === rep;
    const allOk = nameOk && emailOk && strong && match;
    submitBtn.disabled = !allOk;
    submitBtn.style.opacity = allOk ? '1' : '.7';
    submitBtn.style.cursor = allOk ? 'pointer' : 'not-allowed';
  }
  ['input','change'].forEach(evt=>{
    document.addEventListener(evt, e=>{
      if(e.target && ['name','email','password','password_confirmation'].includes(e.target.id)){
        validateForm();
      }
    });
  });
  validateForm();

  form?.addEventListener('submit', ()=>{
    submitBtn?.setAttribute('disabled','disabled');
    spinner?.classList.remove('d-none');
  });

  // Tema local (mesmo do login)
  (function(){
    const root = document.documentElement;
    const btn = document.getElementById('themeToggleRegister');
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
