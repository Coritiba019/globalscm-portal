<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EnsureDigitalAccess
{
    /**
     * Regras:
     * - Pega o digitalId da query (?digital) ou da sessão.
     * - Admin OU users.can_access_all = 1 -> sempre passa.
     * - Caso contrário, checa permissão no pivot user_digital_accounts.
     * - Se não tiver digitalId, força o usuário a selecionar uma conta permitida.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user) {
            return redirect()->route('login');
        }

        // Admins sempre liberados
        if (!empty($user->is_admin) && (int)$user->is_admin === 1) {
            return $next($request);
        }

        // Flag de acesso total
        $canAll = (int)($user->can_access_all ?? 0) === 1;
        if ($canAll) {
            // Mantém/atualiza digital_id na sessão se vier pela query (qualquer conta é válida)
            $qDigital = $this->extractDigitalId($request);
            if ($qDigital > 0) {
                $this->ensureSessionDigital($request, $qDigital);
            }
            return $next($request);
        }

        // Obtém a digital (query tem prioridade sobre a sessão)
        $digitalId = $this->extractDigitalId($request);

        // Se não houver digital definida, forçar seleção de conta permitida
        if ($digitalId <= 0) {
            // Verifica se o usuário possui alguma permissão no pivot
            $hasAny = DB::table('user_digital_accounts')
                ->where('user_id', $user->id)
                ->exists();

            if (!$hasAny) {
                return $this->deny($request, 'Você não tem acesso a nenhuma conta digital.');
            }

            return redirect()->route('select-account')
                ->with('pending_message', 'Selecione uma conta permitida para continuar.');
        }

        // Checa permissão específica dessa conta
        $allowed = DB::table('user_digital_accounts')
            ->where('user_id', $user->id)
            ->where('digital_account_id', $digitalId)
            ->exists();

        if (!$allowed) {
            return $this->deny($request, 'Você não tem permissão para acessar essa conta digital.');
        }

        // Atualiza a sessão com a conta corrente (para as próximas telas)
        $this->ensureSessionDigital($request, $digitalId);

        return $next($request);
    }

    private function extractDigitalId(Request $request): int
    {
        $queryVal   = $request->query('digital');
        $inputVal   = $request->input('digital');
        $sessionVal = $request->session()->get('digital_account_id', 0);

        $digitalId = (int) ($queryVal ?? $inputVal ?? $sessionVal ?? 0);
        return $digitalId > 0 ? $digitalId : 0;
    }

    private function ensureSessionDigital(Request $request, int $digitalId): void
    {
        if ((int)($request->session()->get('digital_account_id') ?? 0) !== $digitalId) {
            $request->session()->put('digital_account_id', $digitalId);
        }
    }

    private function deny(Request $request, string $message)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['message' => $message], 403);
        }
        return redirect()->route('select-account')->with('error', $message);
    }
}
