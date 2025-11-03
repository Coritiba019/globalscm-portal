<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserApproved
{
    /**
     * Permite passar o guard opcional: ->middleware('approved:web') / 'approved:api'
     */
    public function handle(Request $request, Closure $next, ?string $guard = null): Response
    {
        $auth = Auth::guard($guard);
        $user = $auth->user();

        // Usuário convidado? Segue o fluxo normal (quem decide é o middleware 'auth').
        if (!$user) {
            return $next($request);
        }

        // Considere 'APPROVED' como valor aprovado (case-insensitive)
        $status = strtoupper((string) ($user->approval_status ?? ''));
        $isApproved = ($status === 'APPROVED');

        if ($isApproved) {
            return $next($request);
        }

        // Evita loop: permita acessar a própria página de "pending" e logout
        if ($request->routeIs('pending') || $request->routeIs('logout')) {
            return $next($request);
        }

        // Para chamadas API/AJAX, devolve 403 JSON em vez de redirect
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Sua conta ainda não foi aprovada.',
                'status'  => $status ?: 'PENDING',
            ], 403);
        }

        // Se existe a rota 'pending', mantenha o usuário logado e envie para lá
        if (app('router')->has('pending')) {
            return redirect()->route('pending')
                ->with('error', 'Seu acesso ainda não foi aprovado.');
        }

        // Caso não exista rota 'pending', aí sim faça logout e mande para login
        $auth->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('error', 'Seu acesso ainda não foi aprovado.');
    }
}
