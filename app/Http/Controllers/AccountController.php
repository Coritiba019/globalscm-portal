<?php

namespace App\Http\Controllers;

use App\Services\GlobalScmClient;
use App\Support\AccountLabels;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpFoundation\Response as Http;

class AccountController extends Controller
{
    public function pending()
    {
        return view('auth.pending');
    }

    public function selectIndex(Request $request, GlobalScmClient $client)
    {
        $user  = $request->user();
        $limit = (int) $request->query('limit', 25);
        $page  = (int) $request->query('page', 1);
        $q     = trim((string) $request->query('q', ''));

        try {
            // 1) Lista contas (serviço já normaliza)
            $resp   = $client->listAccounts($page, $limit);
            $items  = Arr::get($resp, 'items', []);
            $total  = (int) Arr::get($resp, 'totalItems', count($items));
            $pageN  = (int) Arr::get($resp, 'page', $page);
            $limN   = (int) Arr::get($resp, 'limit', $limit);

            // 2) **Filtra pelas permissões do usuário**
            $allowed = $user->allowedDigitalIds(); // null => acesso a todas
            if (is_array($allowed)) {
                $allowedSet = array_flip($allowed);
                $items = array_values(array_filter($items, function ($row) use ($allowedSet) {
                    $id = (int) Arr::get($row, 'digital_account_id', 0);
                    return $id && isset($allowedSet[$id]);
                }));
                $total = count($items);
            }

            // 3) Injeta rótulo mapeado
            $items = array_map(function (array $acc) {
                $acc['__label'] = AccountLabels::label((string) Arr::get($acc, 'account', ''));
                return $acc;
            }, $items);

            // 4) Filtro de busca local (id/agency/account/company/label)
            if ($q !== '') {
                $needle = mb_strtolower($q);
                $items = array_values(array_filter($items, function ($acc) use ($needle) {
                    $id      = (string) Arr::get($acc, 'digital_account_id', '');
                    $agency  = (string) Arr::get($acc, 'agency', '');
                    $account = (string) Arr::get($acc, 'account', '');
                    $company = (string) Arr::get($acc, 'company.name', '');
                    $label   = (string) Arr::get($acc, '__label', '');
                    $hay = mb_strtolower("$id $agency $account $company $label");
                    return Str::contains($hay, $needle);
                }));
                $total = count($items);
            }

            // 5) Índice de saldos (opcional — top saldos mais recentes)
            $balancesIdx = [];
            foreach ([1, 2] as $pg) {
                $b    = $client->balances($pg, 50);
                $list = Arr::get($b, 'items', []);
                foreach ($list as $row) {
                    $da = (int) Arr::get($row, 'digital_account_id');
                    if (!isset($balancesIdx[$da])) {
                        $balancesIdx[$da] = [
                            'balance'    => Arr::get($row, 'balance'),
                            'dt_balance' => Arr::get($row, 'dt_balance'),
                        ];
                    }
                }
            }

            // 6) Enriquecer itens com saldo e data
            $items = array_map(function ($acc) use ($balancesIdx) {
                $id = (int) Arr::get($acc, 'digital_account_id');
                $acc['__balance']    = Arr::get($balancesIdx, "$id.balance", null);
                $acc['__dt_balance'] = Arr::get($balancesIdx, "$id.dt_balance", null);
                return $acc;
            }, $items);

            // 7) Ordenar por saldo DESC; empates por dt_balance DESC
            usort($items, function(array $a, array $b){
                $aBal = is_null($a['__balance']) ? -INF : (float) $a['__balance'];
                $bBal = is_null($b['__balance']) ? -INF : (float) $b['__balance'];
                if ($aBal !== $bBal) return $bBal <=> $aBal;
                $aDt = strtotime($a['__dt_balance'] ?? '1970-01-01 00:00:00');
                $bDt = strtotime($b['__dt_balance'] ?? '1970-01-01 00:00:00');
                return $bDt <=> $aDt;
            });

            // 8) Paginação
            $paginator = new LengthAwarePaginator(
                $items, $total, $limN, $pageN,
                ['path' => route('select-account'), 'query' => $request->query()]
            );

            // Mensagens de UX quando usuário não tem nenhuma conta permitida
            $noAccessMsg = (empty($items) && !$user->can_access_all)
                ? 'Você não possui acesso a nenhuma conta. Solicite permissão ao administrador.'
                : null;

            return view('accounts.select', [
                'accounts'   => $paginator,
                'totalItems' => $total,
                'page'       => $pageN,
                'limit'      => $limN,
                'q'          => $q,
                'pendingMsg' => session('pending_message'),
                'noAccessMsg'=> $noAccessMsg,
            ]);

        } catch (\Illuminate\Http\Client\RequestException $e) {
            $status = $e->response?->status() ?? 0;
            if (in_array($status, [Http::HTTP_UNAUTHORIZED, Http::HTTP_FORBIDDEN], true)) {
                return redirect()->route('pending')
                    ->with('error', 'Sessão expirada ou sem autorização. Faça login novamente.');
            }
            Log::warning('Falha ao listar contas', ['status' => $status, 'error' => $e->getMessage()]);
            return back()->with('error', 'Não foi possível carregar as contas agora.');
        } catch (\Throwable $e) {
            Log::error('Erro inesperado ao listar contas', ['e' => $e]);
            return back()->with('error', 'Erro inesperado ao carregar as contas.');
        }
    }

    public function selectStore(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'digital_account_id' => ['required','integer','min:1'],
            'agency'  => ['nullable','string','max:32'],
            'account' => ['nullable','string','max:32'],
        ]);

        $digital = (int) $validated['digital_account_id'];

        // **Validação de permissão**: usuário só pode selecionar conta permitida
        if (!$user->can_access_all && !$user->canAccessDigital($digital)) {
            return redirect()->route('select-account')
                ->with('error', 'Conta não permitida para seu usuário.');
        }

        // Persiste na sessão
        $request->session()->put('digital_account_id', $digital);
        $request->session()->put('digital_account_agency', $validated['agency'] ?? null);
        $request->session()->put('digital_account_number', $validated['account'] ?? null);

        return redirect()->route('dashboard')->with('status', 'Conta selecionada com sucesso.');
    }
}
