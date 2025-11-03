@extends('layouts.app')

@section('title', 'Recuperação de senha')

@push('styles')
<style>
  .auth-wrap{min-height:calc(100dvh - 72px);display:grid;place-items:center;padding:2rem 1rem}
  .auth-card{max-width:480px;width:100%;border:1px solid var(--border,#e5e7eb);background:var(--surface,#fff);
    border-radius:16px;box-shadow:0 20px 48px rgba(0,0,0,.12);overflow:hidden}
  .auth-card .card-header{background:linear-gradient(135deg,var(--brand,#0ea5e9),var(--brand-2,#3abff8));border:0;color:#0b1220}
  .brand{font-weight:900;letter-spacing:.2px}
  .badge-soft{border:1px solid var(--border,#e5e7eb);background:var(--surface,#fff);color:var(--muted,#6b7280);
    font-weight:700;padding:.35rem .6rem;border-radius:999px}

  /* Avatar corrigido */
  .avatar-initials{
    width:42px;height:42px;border-radius:999px;
    display:grid;place-items:center;
    font-weight:900;
    color:#0b1220;
    background:linear-gradient(135deg,#ffffff,#dbeafe);
  }
  [data-theme="dark"] .avatar-initials{ color:#0b1220; }
</style>
@endpush

@section('content')
<div class="auth-wrap container">
  <div class="card auth-card">
    <div class="card-header py-3">
      <div class="d-flex align-items-center gap-3">
        <span class="avatar-initials">G</span>
        <div>
          <div class="brand">{{ config('app.name','GlobalPag') }}</div>
          <div class="small" style="opacity:.85;">Recuperação de senha</div>
        </div>
      </div>
    </div>

    <div class="card-body p-4">
      {{-- Mantém compatibilidade com possíveis flashes --}}
      @if (session('status'))
        <div class="alert alert-info">{{ session('status') }}</div>
      @endif

      <div class="alert alert-info">
        <i class="bi bi-info-circle me-1"></i>
        A recuperação de senha está temporariamente indisponível. Em breve ativaremos este recurso.
      </div>

      <p class="text-muted mb-4">
        Caso precise de acesso imediato, entre em contato com o suporte do sistema.
      </p>

      <div class="d-grid">
        <a href="{{ route('login') }}" class="btn btn-primary">
          <i class="bi bi-box-arrow-in-right me-1"></i> Voltar ao login
        </a>
      </div>

      <div class="mt-4 small">
        <span class="badge-soft"><i class="bi bi-shield-lock me-1"></i> Segurança</span>
        <ul class="mt-2 mb-0 ps-3 text-muted">
          <li>Conexão protegida por HTTPS.</li>
          <li>Ative 2FA quando disponível para mais segurança.</li>
        </ul>
      </div>
    </div>
  </div>
</div>
@endsection
