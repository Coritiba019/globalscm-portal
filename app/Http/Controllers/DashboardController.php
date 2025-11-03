<?php

namespace App\Http\Controllers;

use App\Services\GlobalScmClient;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;

class DashboardController extends Controller
{
    public function index(Request $request, GlobalScmClient $client)
    {
        $digital = (int) ($request->session()->get('digital_account_id') ?? 0);
        if (!$digital) {
            return redirect()->route('select-account')
                ->with('pending_message', 'Selecione uma conta para visualizar o dashboard.');
        }

        // Período padrão: últimos 7 dias
        $tz      = 'America/Sao_Paulo';
        $final   = Carbon::now($tz)->toDateString();
        $initial = Carbon::now($tz)->subDays(6)->toDateString();

        // KPIs e séries
        $labels = [];
        $values = [];
        $kpi_total_days = 0;
        $kpi_total_tx   = 0;

        /**
         * 1) Daily report (POST /internal/api/v1/reports/daily-transactions)
         *    Se falhar ou vier vazio, fazemos fallback para o agregador local (dailySummary()).
         */
        try {
            $dailyResp = $client->dailyReport([
                'digitalAccountId' => (string) $digital,
                // Descomente se a API exigir datas explicitamente:
                // 'initialDate'      => $initial,
                // 'finalDate'        => $final,
            ]);

            // Formato esperado:
            // data: [ { dia: ISO, total_transacoes: "123" }, ... ]
            // summary: { totalDays, totalTransactions }
            $dailyRows = Arr::get($dailyResp, 'data', []);
            $summary   = Arr::get($dailyResp, 'summary', ['totalDays'=>0,'totalTransactions'=>0]);

            if (!empty($dailyRows)) {
                foreach ($dailyRows as $r) {
                    $diaISO = Arr::get($r, 'dia');
                    if (!$diaISO) continue;
                    $dt = Carbon::parse($diaISO)->timezone($tz);
                    $labels[] = $dt->format('d/m');
                    $values[] = (int) Arr::get($r, 'total_transacoes', 0);
                }
                $kpi_total_days = (int) Arr::get($summary, 'totalDays', 0);
                $kpi_total_tx   = (int) Arr::get($summary, 'totalTransactions', 0);
            } else {
                throw new \RuntimeException('Daily report vazio – usando fallback');
            }
        } catch (\Throwable $e) {
            // Fallback local via /transactions
            $local = $client->dailySummary([
                'digitalAccountId' => (int) $digital,
                'initialDate'      => $initial,
                'finalDate'        => $final,
                // 'status'        => 'SUCCESS',
                // 'subtype'       => 'PIX',
            ]);
            $series = Arr::get($local, 'series', []);
            foreach ($series as $row) {
                $labels[] = (string) Arr::get($row, 'dia_br');
                $values[] = (int) Arr::get($row, 'total', 0);
            }
            $kpi_total_days = count($series);
            $kpi_total_tx   = (int) Arr::get($local, 'totals.items', 0);
        }

        /**
         * 2) Últimas transações no período (GET /transactions)
         */
        $txResp  = $client->transactions([
            'initialDate'      => $initial,
            'finalDate'        => $final,
            'page'             => 1,
            'limit'            => 25,
            'digitalAccountId' => (string) $digital,
        ]);
        $txItems = $txResp['items'] ?? Arr::get($txResp, 'data.transactions', []);

        /**
         * 3) Saldo atual da conta selecionada (filtra pela conta)
         */
        $balResp = $client->balances(1, 50, $digital);
        $currentBalance = null;
        $balItems = $balResp['items'] ?? Arr::get($balResp, 'list', []);
        if (!empty($balItems)) {
            $first = $balItems[0];
            $currentBalance = Arr::get($first, 'balance');
        }

        $kpi_balance = $currentBalance !== null
            ? number_format((float)$currentBalance, 2, ',', '.')
            : '—';

        return view('dashboard.index', [
            'digital' => $digital,
            'account' => (object)[
                'agencyNumber'  => $request->session()->get('digital_account_agency') ?? '—',
                'accountNumber' => $request->session()->get('digital_account_number') ?? '—',
            ],
            'kpis' => [
                'total_days' => $kpi_total_days,
                'total_tx'   => $kpi_total_tx,
                'balance'    => $kpi_balance,
            ],
            'labels' => $labels,
            'values' => $values,
            'tx'     => $txItems,
            'period' => compact('initial', 'final'),
        ]);
    }

    /**
     * Endpoint JSON para listar transações com filtros (para uso via AJAX no front).
     * GET /dashboard/transactions?initialDate=YYYY-MM-DD&finalDate=YYYY-MM-DD&digital=ID&page=1&limit=25&status=...&subtype=...
     */
    public function transactions(Request $request, GlobalScmClient $client): JsonResponse
    {
        $digital     = (int) $request->query('digital', (int) $request->session()->get('digital_account_id', 0));
        $initialDate = (string) $request->query('initialDate', '');
        $finalDate   = (string) $request->query('finalDate', '');
        $page        = (int) $request->query('page', 1);
        $limit       = (int) $request->query('limit', 25);
        $status      = $request->query('status');   // opcional
        $subtype     = $request->query('subtype');  // opcional

        if (!$digital) {
            return Response::json(['error' => 'Selecione uma conta'], 400);
        }

        try {
            $resp = $client->transactions([
                'page'             => $page,
                'limit'            => $limit,
                'initialDate'      => $initialDate ?: null,
                'finalDate'        => $finalDate ?: null,
                'status'           => $status ?: null,
                'subtype'          => $subtype ?: null,
                'digitalAccountId' => $digital,
            ]);

            // Normalizar para uma estrutura consistente
            $items = $resp['items']
                ?? Arr::get($resp, 'data.transactions', []);
            $total = $resp['totalItems']
                ?? Arr::get($resp, 'total', count($items));

            return Response::json([
                'page'   => $page,
                'limit'  => $limit,
                'total'  => $total,
                'items'  => $items,
                'filters'=> [
                    'initialDate' => $initialDate ?: null,
                    'finalDate'   => $finalDate   ?: null,
                    'status'      => $status      ?: null,
                    'subtype'     => $subtype     ?: null,
                    'digital'     => $digital,
                ],
            ]);
        } catch (\Throwable $e) {
            return Response::json([
                'error' => 'Falha ao consultar transações',
                'detail' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Endpoint JSON para série diária (para gráficos/relatórios no front).
     * GET /dashboard/daily?initialDate=YYYY-MM-DD&finalDate=YYYY-MM-DD&digital=ID
     */
    public function daily(Request $request, GlobalScmClient $client): JsonResponse
    {
        $digital     = (int) $request->query('digital', (int) $request->session()->get('digital_account_id', 0));
        $initialDate = (string) $request->query('initialDate', '');
        $finalDate   = (string) $request->query('finalDate', '');
        $tz          = 'America/Sao_Paulo';

        if (!$digital) {
            return Response::json(['error' => 'Selecione uma conta'], 400);
        }

        try {
            // Tentar API nativa de reports
            $dailyResp = $client->dailyReport([
                'digitalAccountId' => (string) $digital,
                'initialDate'      => $initialDate ?: null,
                'finalDate'        => $finalDate   ?: null,
            ]);

            $rows    = Arr::get($dailyResp, 'data', []);
            $summary = Arr::get($dailyResp, 'summary', ['totalDays'=>0,'totalTransactions'=>0]);

            if (!empty($rows)) {
                $series = [];
                foreach ($rows as $r) {
                    $iso = Arr::get($r, 'dia');
                    if (!$iso) continue;
                    $dt = Carbon::parse($iso)->timezone($tz);
                    $series[] = [
                        'dia_br' => $dt->format('d/m'),
                        'dia_iso'=> $dt->toDateString(),
                        'total'  => (int) Arr::get($r, 'total_transacoes', 0),
                    ];
                }

                return Response::json([
                    'series' => $series,
                    'totals' => [
                        'days'  => (int) Arr::get($summary, 'totalDays', 0),
                        'items' => (int) Arr::get($summary, 'totalTransactions', 0),
                    ],
                    'filters'=> [
                        'initialDate' => $initialDate ?: null,
                        'finalDate'   => $finalDate   ?: null,
                        'digital'     => $digital,
                    ],
                ]);
            }

            // Se vier vazio, cai no fallback
            throw new \RuntimeException('Daily nativo vazio');
        } catch (\Throwable $e) {
            // Fallback: agregador local a partir de /transactions
            try {
                $local = $client->dailySummary([
                    'digitalAccountId' => (int) $digital,
                    'initialDate'      => $initialDate ?: null,
                    'finalDate'        => $finalDate   ?: null,
                ]);

                return Response::json([
                    'series'  => Arr::get($local, 'series', []),
                    'totals'  => Arr::get($local, 'totals', ['days'=>0,'items'=>0]),
                    'filters' => [
                        'initialDate' => $initialDate ?: null,
                        'finalDate'   => $finalDate   ?: null,
                        'digital'     => $digital,
                    ],
                    'source'  => 'local',
                ]);
            } catch (\Throwable $e2) {
                return Response::json([
                    'error'  => 'Falha ao gerar série diária',
                    'detail' => $e2->getMessage(),
                ], 500);
            }
        }
    }
}
