{{-- resources/views/auth/pending.blade.php --}}
@extends('layouts.app')

@section('title', 'Aguardando aprovação')

@push('styles')
<style>
  .pending-wrap{
    min-height:calc(100dvh - 72px);
    display:grid; place-items:center;
    padding:2rem 1rem;
  }
  .pending-card{
    max-width:560px;width:100%;
    border:1px solid var(--border);
    background:var(--surface);
    border-radius:16px;
    box-shadow:0 20px 48px rgba(0,0,0,.12);
    overflow:hidden;
  }
  .pending-card .card-header{
    background:linear-gradient(135deg,var(--brand,#0ea5e9),var(--brand-2,#3abff8));
    border:0; color:#0b1220;
  }
  .badge-soft{
    border:1px solid var(--border);
    background:var(--surface);
    color:var(--muted);
    font-weight:700;
    padding:.35rem .6rem;
    border-radius:999px;
  }
</style>
@endpush

@section('content')
<div class="pending-wrap container">
  <div class="card pending-card" role="region" aria-labelledby="pendingTitle">
    <div class="card-header py-3">
      <div class="d-flex align-items-center gap-3">
        {{-- Avatar centralizado usando a classe global do layout --}}
        <span class="avatar-initials" style="width:42px;height:42px;">
          @php
            $brand = config('app.name','GlobalPag');
            echo mb_strtoupper(mb_substr($brand, 0, 1));
          @endphp
        </span>
        <div>
          <div id="pendingTitle" class="fw-bold">{{ config('app.name','GlobalPag') }}</div>
          <div class="small" style="opacity:.85;">Aguardando aprovação</div>
        </div>
      </div>
    </div>

    <div class="card-body p-4">
      {{-- Alertas --}}
      @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
      @endif
      @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
      @endif

      {{-- Mensagem principal --}}
      <p class="mb-2">Seu cadastro foi recebido e está em análise.</p>
      <p class="mb-3 text-muted">
        Assim que sua conta for <strong>aprovada</strong>, você poderá acessar todas as funcionalidades.
      </p>

      {{-- Ações --}}
      <div class="d-flex gap-2">
        @if(Route::has('logout'))
          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="btn btn-outline-secondary">
              <i class="bi bi-box-arrow-left me-1"></i> Sair
            </button>
          </form>
        @endif

        <a href="{{ route('login') }}" class="btn btn-primary">
          <i class="bi bi-arrow-repeat me-1"></i> Tentar novamente mais tarde
        </a>
      </div>

      {{-- Status (opcionalmente dinâmico) --}}
      <div class="mt-4 small">
        <span class="badge-soft">
          <i class="bi bi-shield-check me-1"></i> Status
        </span>
        <div class="text-muted mt-2">
          Status atual da sua conta:
          <strong>
            @php
              // Se houver um campo status no User, use-o; senão, exibe PENDING
              $status = optional(auth()->user())->status ?? 'PENDING';
              echo e($status);
            @endphp
          </strong>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
