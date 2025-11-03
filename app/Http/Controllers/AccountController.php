<?php

namespace App\Http\Controllers;

use App\Services\GlobalScmClient;
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
        $limit = (int) $request->query('limit', 25);
        $page  = (int) $request->query('page', 1);
        $q     = trim((string) $request->query('q', ''));

        try {
            // 1) Busca contas na API externa (formato normalizado pelo service)
            $resp   = $client->listAccounts($page, $limit);
            $items  = Arr::get($resp, 'items', []);      // normalizados: digital_account_id, agency, account, digit, company, active...
            $total  = (int) Arr::get($resp, 'totalItems', count($items));
            $pageN  = (int) Arr::get($resp, 'page', $page);
            $limN   = (int) Arr::get($resp, 'limit', $limit);

            // 2) Filtro leve no servidor se tiver "q"
            if ($q !== '') {
                $needle = mb_strtolower($q);
                $items = array_values(array_filter($items, function ($acc) use ($needle) {
                    $id      = (string) Arr::get($acc, 'digital_account_id', '');
                    $agency  = (string) Arr::get($acc, 'agency', '');
                    $account = (string) Arr::get($acc, 'account', '');
                    $company = (string) Arr::get($acc, 'company.name', '');
                    $hay = mb_strtolower("$id $agency $account $company");
                    return Str::contains($hay, $needle);
                }));
                // quando filtramos localmente, ajusta total
                $total = count($items);
            }

            // 3) Índice de saldos (duas páginas p/ ~100 últimas posições)
            $balancesIdx = [];
            foreach ([1, 2] as $pg) {
                $b    = $client->balances($pg, 50);
                $list = Arr::get($b, 'items', []);
                foreach ($list as $row) {
                    $da = (int) Arr::get($row, 'digital_account_id');
                    // mantém o primeiro (já ordenado por dt_balance desc)
                    if (!isset($balancesIdx[$da])) {
                        $balancesIdx[$da] = [
                            'balance'    => Arr::get($row, 'balance'),
                            'dt_balance' => Arr::get($row, 'dt_balance'),
                        ];
                    }
                }
            }

            // 4) Enriquecer itens com __balance / __dt_balance
            $items = array_map(function ($acc) use ($balancesIdx) {
                $id = (int) Arr::get($acc, 'digital_account_id');
                $acc['__balance']    = Arr::get($balancesIdx, "$id.balance", null);
                $acc['__dt_balance'] = Arr::get($balancesIdx, "$id.dt_balance", null);
                return $acc;
            }, $items);

            // 5) Paginação Laravel (LengthAwarePaginator) — mantém querystring
            $paginator = new LengthAwarePaginator(
                $items,
                $total,
                $limN,
                $pageN,
                [
                    'path'  => route('select-account'),
                    'query' => $request->query(), // preserva q/limit
                ]
            );

            return view('accounts.select', [
                'accounts'   => $paginator,      // agora tem ->links()
                'totalItems' => $total,
                'page'       => $pageN,
                'limit'      => $limN,
                'q'          => $q,
                'pendingMsg' => session('pending_message'),
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
        $validated = $request->validate([
            'digital_account_id' => ['required','integer','min:1'],
            'agency'  => ['nullable','string','max:32'],
            'account' => ['nullable','string','max:32'],
        ]);

        $request->session()->put('digital_account_id', (int) $validated['digital_account_id']);
        $request->session()->put('digital_account_agency', $validated['agency'] ?? null);
        $request->session()->put('digital_account_number', $validated['account'] ?? null);

        return redirect()->route('dashboard')->with('status', 'Conta selecionada com sucesso.');
    }
}
