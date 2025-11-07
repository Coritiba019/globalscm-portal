<?php

namespace App\Http\Controllers;

use App\Services\GlobalScmClient;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpFoundation\Response as Http;

class TransactionController extends Controller
{
    public function index(Request $request, GlobalScmClient $client)
    {
        // 1) Contexto: digital selecionado (igual ao restante do portal)
        $digital = (int) ($request->query('digital') ?: ($request->session()->get('digital_account_id') ?? 0));
        if (!$digital) {
            return redirect()->route('select-account')
                ->with('pending_message', 'Selecione uma conta para visualizar as transações.');
        }

        // 2) Filtros de período
        // Regra nova: se não vierem datas, carrega somente o "último dia" (hoje) para performance.
        $tz      = 'America/Sao_Paulo';
        $initial = $request->query('initial');
        $final   = $request->query('final');

        if (!$initial || !$final) {
            // Um único dia (hoje, no fuso de SP)
            $today   = Carbon::now($tz)->toDateString();
            $initial = $today;
            $final   = $today;
        }

        // Safe-guard: se o usuário mandar initial > final, invertemos
        if (strtotime($initial) > strtotime($final)) {
            [$initial, $final] = [$final, $initial];
        }

        // 3) Tipo / SubTipo / Status (opcionais)
        $type    = (string) $request->query('type', 'todos'); // credit|debit|todos
        $subType = (string) $request->query('subType', '');
        $status  = (string) $request->query('status', '');

        // 4) Paginação (10 até 1000)
        $limit = (int) $request->query('limit', 25);
        if (!in_array($limit, [10, 25, 50, 100, 200, 500, 1000], true)) $limit = 25;

        $page = max(1, (int) $request->query('page', 1));

        // 5) Monta params do endpoint
        $query = [
            'initialDate' => $initial,
            'finalDate'   => $final,
            'page'        => $page,
            'limit'       => $limit,
        ];

        // Observação: se o upstream suportar, já deixamos os filtros preparados
        if ($type === 'credit')   $query['type'] = 'C';
        if ($type === 'debit')    $query['type'] = 'D';
        if ($subType !== '')      $query['subType'] = $subType;
        if ($status !== '')       $query['status']  = $status;

        try {
            // 6) Chama serviço (garante token internamente)
            $resp = $client->transactions($query);

            // payload esperado:
            // {
            //   "page": 1,
            //   "limit": "25",
            //   "size": 68,
            //   "data": {
            //     "balance": {...},
            //     "transactions": [ {...}, ... ]
            //   }
            // }

            $apiPage   = (int) ($resp['page']  ?? $page);
            $apiLimit  = (int) ($resp['limit'] ?? $limit);
            $apiSize   = (int) ($resp['size']  ?? 0);
            $data      = Arr::get($resp, 'data', []);
            $balance   = Arr::get($data, 'balance', null);
            $items     = Arr::get($data, 'transactions', []);

            // 7) Paginador local a partir da API
            $paginator = new LengthAwarePaginator(
                $items,
                $apiSize,               // total
                $apiLimit ?: $limit,    // por página
                $apiPage ?: $page,      // página atual
                ['path' => url()->current(), 'query' => $request->query()]
            );

            // 8) Resumo para KPIs
            $summary = [
                'count'         => is_countable($items) ? count($items) : 0,
                'total'         => $apiSize,
                'credit'        => (float) ($balance['credit'] ?? 0),
                'debit'         => (float) ($balance['debit'] ?? 0),
                'balance'       => (float) ($balance['balance'] ?? 0),
                'dtBalance'     => (string) ($balance['dtBalance'] ?? ''),
                'corporateName' => (string) ($balance['corporateName'] ?? ''),
            ];

            // 9) View
            return view('transactions.index', [
                'digital'   => $digital,
                'initial'   => $initial,
                'final'     => $final,
                'type'      => $type,
                'subType'   => $subType,
                'status'    => $status,
                'limit'     => $limit,
                'paginator' => $paginator,
                'rows'      => $items,
                'balance'   => $balance,
                'summary'   => $summary,
                'filters'   => [
                    'type'    => $type,
                    'subType' => $subType,
                    'status'  => $status,
                    'initial' => $initial,
                    'final'   => $final,
                    'limit'   => $limit,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::warning('[transactions] falha ao consultar', [
                'err'  => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return back()
                ->with('error', 'Não foi possível obter as transações agora.')
                ->setStatusCode(Http::HTTP_OK);
        }
    }
}
