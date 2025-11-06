<?php

namespace App\Services;

use App\Models\GlobalSetting;
use Carbon\CarbonImmutable;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class GlobalScmClient
{
    private Client $http;
    private GlobalSetting $cfg;

    /** Hosts */
    private const HOST_BACKOFFICE = 'https://admin-backoffice-api-prod.globalscm.app.br';
    private const HOST_LEGACY     = 'https://api.globalscm.app.br';

    public function __construct()
    {
        $this->cfg = GlobalSetting::query()->firstOrFail();

        // Sem base_uri: montamos URL absoluta a cada request.
        $this->http = new Client([
            'timeout'     => 30,
            'http_errors' => false,
            'headers'     => [
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json',
                'mobile-app'   => 'true',
            ],
        ]);
    }

    /** ===================== HOST ROUTER ===================== */

    private function pickHost(string $method, string $path): string
    {
        $method = strtoupper($method);

        // Backoffice (novo)
        if (
            ($method === 'POST' && str_starts_with($path, '/internal/api/v1/reports/')) ||
            ($method === 'GET'  && $path === '/internal/api/v1/transactions') ||
            ($method === 'GET'  && $path === '/internal/api/v1/transactions/status/v2')
        ) {
            return self::HOST_BACKOFFICE;
        }

        // Legacy (ainda lá)
        if (
            ($method === 'POST' && $path === '/internal/api/v1/auth/login') ||
            ($method === 'GET'  && $path === '/internal/api/v1/account')   ||
            ($method === 'GET'  && $path === '/internal/api/v1/balances')  // <- balances vai para LEGACY
        ) {
            return self::HOST_LEGACY;
        }

        // Fallback — preferir Backoffice
        return self::HOST_BACKOFFICE;
    }

    private function buildUrl(string $method, string $path): string
    {
        $base = rtrim($this->pickHost($method, $path), '/');
        return $base . $path;
    }

    /** ======================== AUTH ========================= */

    /** Garante que há token válido antes de chamar a API */
    private function ensureToken(): void
    {
        $needLogin = empty($this->cfg->access_token);

        if (!$needLogin && $this->cfg->token_expires_at) {
            $expires = CarbonImmutable::parse($this->cfg->token_expires_at);
            if ($expires->isBefore(now()->addMinutes(2))) {
                $needLogin = true;
            }
        }

        if ($needLogin) {
            $this->loginAndPersist();
        }
    }

    /** Faz login (LEGACY) e persiste token/expiração em global_settings */
    private function loginAndPersist(): void
    {
        $body = [
            'account'  => $this->cfg->service_account,
            'password' => $this->cfg->service_password,
        ];

        $loginPath = '/internal/api/v1/auth/login';
        $url       = $this->buildUrl('POST', $loginPath);

        try {
            $res = $this->http->post($url, ['json' => $body]);
        } catch (GuzzleException $e) {
            throw new \RuntimeException('Falha ao autenticar na API externa: ' . $e->getMessage(), 0, $e);
        }

        $status = $res->getStatusCode();
        $raw    = (string) $res->getBody();
        $json   = json_decode($raw, true);

        if ($status >= 400) {
            Log::error('Login externo falhou', ['status' => $status, 'body' => $raw]);
            throw new \RuntimeException('Login externo falhou (status ' . $status . ').');
        }

        if (!is_array($json)) {
            Log::error('Login externo retornou payload inválido', ['body' => $raw]);
            throw new \RuntimeException('Resposta de login inválida.');
        }

        $token   = Arr::get($json, 'token') ?? Arr::get($json, 'accessToken') ?? Arr::get($json, 'access_token');
        $expires = Arr::get($json, 'expiresAt') ?? Arr::get($json, 'expires_in');

        if (!$token) {
            throw new \RuntimeException('Resposta de login sem token.');
        }

        // 1) Tenta extrair exp do JWT
        $expiresAt = self::jwtExpOrNull($token);

        // 2) Se não houver, usa heurística
        if (!$expiresAt) {
            if (is_numeric($expires)) {
                $n = (int) $expires;
                $expiresAt = $n > 60 * 60 * 24 * 30
                    ? CarbonImmutable::createFromTimestampUTC($n)
                    : now()->addSeconds($n);
            } elseif (is_string($expires)) {
                $expiresAt = CarbonImmutable::parse($expires);
            } else {
                $expiresAt = now()->addHours(8);
            }
        }

        $this->cfg->fill([
            'access_token'     => $token,
            'token_expires_at' => $expiresAt,
        ])->save();
    }

    /** Lê exp (UTC) de um JWT, ou null se não houver. */
    private static function jwtExpOrNull(string $jwt): ?CarbonImmutable
    {
        try {
            $parts = explode('.', $jwt);
            if (count($parts) < 2) {
                return null;
            }
            $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
            if (isset($payload['exp']) && is_numeric($payload['exp'])) {
                return CarbonImmutable::createFromTimestampUTC((int) $payload['exp']);
            }
        } catch (\Throwable $e) {
            // ignora parsing errors
        }
        return null;
    }

    /** ===================== CORE REQUEST ==================== */

    /**
     * Faz request com Bearer (renova em 401) e retorna array.
     * Suporta a opção interna `_allow_error` para não lançar exceção em 4xx/5xx.
     */
    private function request(string $method, string $path, array $options = [])
    {
        $this->ensureToken();

        $allowError = (bool)($options['_allow_error'] ?? false);
        unset($options['_allow_error']);

        $options['headers'] = array_merge(
            $options['headers'] ?? [],
            ['Authorization' => 'Bearer ' . $this->cfg->access_token]
        );

        $url = $this->buildUrl($method, $path);

        try {
            $res = $this->http->request($method, $url, $options);
        } catch (GuzzleException $e) {
            throw new \RuntimeException("Erro HTTP ao chamar {$path}: " . $e->getMessage(), 0, $e);
        }

        if ($res->getStatusCode() === 401) {
            // tenta renovar e repetir uma vez
            $this->loginAndPersist();
            $options['headers']['Authorization'] = 'Bearer ' . $this->cfg->access_token;
            $res = $this->http->request($method, $url, $options);
        }

        $status = $res->getStatusCode();
        $raw    = (string) $res->getBody();
        $data   = json_decode($raw, true);

        if ($status >= 400) {
            Log::warning('Chamada externa retornou erro', ['path' => $path, 'status' => $status, 'body' => $raw]);
            if ($allowError) {
                return is_array($data) ? $data : ['_error_status' => $status, '_raw' => $raw];
            }
            throw new \RuntimeException("API externa respondeu {$status} para {$path}");
        }

        return is_array($data) ? $data : [];
    }

    /** ==================== MÉTODOS PÚBLICOS ================= */

    /** Backoffice (POST) */
    public function dailyReport(array $payload): array
    {
        return $this->request('POST', '/internal/api/v1/reports/daily-transactions', [
            'json' => $payload,
        ]);
    }

    /**
     * Listar contas (LEGACY, GET /internal/api/v1/account) — normalizado.
     * Retorna: ['page','limit','totalItems','items'=>[...]]
     */
    public function listAccounts(int $page = 1, int $limit = 10): array
    {
        $query = ['page' => $page, 'limit' => $limit];
        $data  = $this->request('GET', '/internal/api/v1/account', ['query' => $query]);

        $list = Arr::get($data, 'list', []);
        if (!is_array($list)) {
            $list = [];
        }

        $items = array_map(function (array $row) {
            return [
                'digital_account_id' => (int)($row['id'] ?? 0),
                'agency'             => (string)($row['agencyNumber'] ?? ''),
                'account'            => (string)($row['accountNumber'] ?? ''),
                'digit'              => (string)($row['digitAccount'] ?? ''),
                'active'             => (bool)($row['active'] ?? false),
                'company'            => [
                    'name'    => (string)($row['company_id']['corporateName'] ?? ''),
                    'cpfCnpj' => (string)($row['company_id']['cpfCnpj'] ?? ''),
                    'id'      => (int)($row['company_id']['id'] ?? 0),
                ],
                '_raw'               => $row,
            ];
        }, $list);

        return [
            'page'       => (int)($data['current_page'] ?? $data['page'] ?? 1),
            'limit'      => (int)($data['total_per_pages'] ?? $data['limit'] ?? $limit),
            'totalItems' => (int)($data['totalItems'] ?? $data['total_current'] ?? count($items)),
            'items'      => $items,
            '_raw'       => $data,
        ];
    }

    /** Transações paginadas (Backoffice, GET) — normalizado */
    public function transactions(array $params): array
    {
        $query = [
            'page'             => $params['page'] ?? 1,
            'limit'            => $params['limit'] ?? 25,
            'initialDate'      => $params['initialDate'] ?? null,
            'finalDate'        => $params['finalDate'] ?? null,
            'status'           => $params['status'] ?? null,
            'subtype'          => $params['subtype'] ?? null,
            'q'                => $params['q'] ?? null,
            'digitalAccountId' => $params['digitalAccountId'] ?? null,
            'type'             => $params['type'] ?? null,
        ];
        $query = array_filter($query, fn($v) => $v !== null && $v !== '');

        $data = $this->request('GET', '/internal/api/v1/transactions', ['query' => $query]);

        $page       = (int)($data['page'] ?? 1);
        $limit      = (int)($data['limit'] ?? ($data['size'] ?? 25));
        $totalItems = (int)($data['totalItems'] ?? 0);

        $items = $data['items']
            ?? Arr::get($data, 'data.transactions')
            ?? Arr::get($data, 'list')
            ?? [];

        return [
            'page'       => $page,
            'limit'      => $limit,
            'totalItems' => $totalItems,
            'items'      => is_array($items) ? $items : [],
            '_raw'       => $data,
        ];
    }

    /**
     * Saldos (LEGACY, GET /internal/api/v1/balances) — normalizado.
     */
    public function balances(int $page = 1, int $limit = 50, ?int $digitalAccountId = null): array
    {
        $query = [
            'page'  => $page,
            'limit' => $limit,
        ];
        if ($digitalAccountId) {
            $query['digitalAccountId'] = $digitalAccountId;
        }

        $data = $this->request('GET', '/internal/api/v1/balances', ['query' => $query]);

        $pageN  = (int)($data['current_page'] ?? $data['page'] ?? 1);
        $limitN = (int)($data['total_per_pages'] ?? $data['limit'] ?? $limit);
        $total  = (int)($data['total_current'] ?? $data['totalItems'] ?? 0);

        $list = Arr::get($data, 'list') ?? Arr::get($data, 'items') ?? [];
        $list = is_array($list) ? $list : [];

        // Ordena do mais recente pro mais antigo (quando houver data)
        usort($list, function ($a, $b) {
            $da = strtotime($a['dt_balance'] ?? $a['date'] ?? '1970-01-01');
            $db = strtotime($b['dt_balance'] ?? $b['date'] ?? '1970-01-01');
            return $db <=> $da;
        });

        $items = array_map(function ($row) {
            $company = $row['company_id'] ?? $row['company'] ?? [];
            return [
                'digital_account_id' => (int)($row['digital_account_id'] ?? $row['digitalAccountId'] ?? 0),
                'dt_balance'         => (string)($row['dt_balance'] ?? $row['date'] ?? ''),
                'balance'            => (float)($row['balance'] ?? 0),
                'credit'             => (float)($row['credit'] ?? 0),
                'debit'              => (float)($row['debit'] ?? 0),
                'company'            => [
                    'id'          => (int)($company['id'] ?? 0),
                    'name'        => (string)($company['corporateName'] ?? $company['name'] ?? ''),
                    'cpfCnpj'     => (string)($company['cpfCnpj'] ?? ''),
                    'type'        => (string)($company['type'] ?? ''),
                    'accountType' => (string)($company['accountType'] ?? ''),
                ],
                '_raw'               => $row,
            ];
        }, $list);

        return [
            'page'       => $pageN,
            'limit'      => $limitN,
            'totalItems' => $total,
            'items'      => $items,
            '_raw'       => $data,
        ];
    }

    /** Status v2 (Backoffice, GET) */
    public function transactionStatusV2(array $query): array
    {
        $q = array_filter([
            'clientRequestId' => $query['clientRequestId'] ?? null,
            'endToEndId'      => $query['endToEndId'] ?? null,
        ], fn($v) => $v !== null && $v !== '');

        return $this->request('GET', '/internal/api/v1/transactions/status/v2', ['query' => $q]);
    }

    /** Report de transações (Backoffice, POST) */
    public function transactionsReport(array $payload): array
    {
        return $this->request('POST', '/internal/api/v1/reports/transactions', [
            'json' => array_filter($payload, fn($v) => $v !== null && $v !== ''),
        ]);
    }

    /** Resumo diário agregado localmente (usa transactions GET) */
    public function dailySummary(array $params): array
    {
        $tz          = 'America/Sao_Paulo';
        $finalDate   = $params['finalDate']   ?? now($tz)->toDateString();
        $initialDate = $params['initialDate'] ?? now($tz)->subDays(6)->toDateString();
        $digital     = $params['digitalAccountId'] ?? null;

        $page    = 1;
        $limit   = 500;
        $hardCap = 20000;
        $got     = 0;
        $all     = [];

        while (true) {
            $resp = $this->transactions([
                'page'             => $page,
                'limit'            => $limit,
                'initialDate'      => $initialDate,
                'finalDate'        => $finalDate,
                'digitalAccountId' => $digital,
                'status'           => $params['status']  ?? null,
                'subtype'          => $params['subtype'] ?? null,
                'type'             => $params['type']    ?? null,
            ]);

            $batch = $resp['items'] ?? [];
            if (empty($batch)) {
                break;
            }

            $all = array_merge($all, $batch);
            $got += count($batch);
            if ($got >= $hardCap) {
                break;
            }

            $page++;
        }

        $countByDay  = [];
        $amountByDay = [];

        foreach ($all as $row) {
            $rawDate = Arr::get($row, 'dt_hr_transaction')
                ?? Arr::get($row, 'createdAt')
                ?? Arr::get($row, 'date')
                ?? Arr::get($row, 'dateCreated')
                ?? Arr::get($row, 'created_at');

            if (!$rawDate) {
                continue;
            }

            try {
                $day = \Carbon\Carbon::parse($rawDate)->timezone($tz)->toDateString();
            } catch (\Throwable $e) {
                continue;
            }

            $amount = (float)(
                $row['amount']
                ?? $row['value']
                ?? $row['valor']
                ?? 0
            );

            $countByDay[$day]  = ($countByDay[$day]  ?? 0) + 1;
            $amountByDay[$day] = ($amountByDay[$day] ?? 0) + $amount;
        }

        $cursor = \Carbon\Carbon::parse($initialDate, $tz)->startOfDay();
        $end    = \Carbon\Carbon::parse($finalDate,   $tz)->startOfDay();

        $series      = [];
        $totalItems  = 0;
        $totalAmount = 0.0;

        while ($cursor->lte($end)) {
            $dIso = $cursor->toDateString();
            $qty  = (int)($countByDay[$dIso]   ?? 0);
            $amt  = (float)($amountByDay[$dIso] ?? 0);

            $series[] = [
                'dia_iso' => $dIso,
                'dia_br'  => $cursor->format('d/m/Y'),
                'total'   => $qty,
                'amount'  => round($amt, 2),
            ];

            $totalItems  += $qty;
            $totalAmount += $amt;

            $cursor->addDay();
        }

        return [
            'series'      => $series,
            'totals'      => [
                'items'  => $totalItems,
                'amount' => round($totalAmount, 2),
            ],
            'countByDay'  => $countByDay,
            'amountByDay' => $amountByDay,
            'range'       => compact('initialDate', 'finalDate'),
        ];
    }
}
