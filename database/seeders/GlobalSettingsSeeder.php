<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\GlobalSetting;

class GlobalSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // === 1) Parâmetros fixos para o seed (sem .env) ======================
        $apiBase  = 'https://api.globalscm.app.br';

        // Credenciais que você já validou via cURL
        $account  = '00100001674';
        $password = 'my10Mcr8YYWA';

        // === 2) Cria (ou obtém) o registro singleton =========================
        $settings = GlobalSetting::firstOrCreate([], [
            'api_base'         => $apiBase,
            'service_account'  => $account,   // mantém no DB para futuras renovações via comando/scheduler
            'service_password' => $password,
        ]);

        // sempre garante api_base atualizado
        if ($settings->api_base !== $apiBase) {
            $settings->api_base = $apiBase;
        }

        // === 3) Login na API externa para obter access_token ==================
        $resp = Http::retry(3, 500)
            ->timeout(30)
            ->post(rtrim($apiBase, '/').'/internal/api/v1/auth/login', [
                'account'  => $account,
                'password' => $password,
            ]);

        if (!$resp->successful()) {
            $body = $resp->body();
            throw new \RuntimeException("Falha ao autenticar na GlobalSCM: HTTP {$resp->status()} - {$body}");
        }

        $json = $resp->json();

        // A API retorna "access_token" (e "expires_in" em alguns casos)
        $accessToken = $json['access_token'] ?? null;
        if (!$accessToken) {
            throw new \RuntimeException("Resposta sem access_token: ".json_encode($json));
        }

        // === 4) Decodifica 'exp' do JWT (se existir) para token_expires_at ===
        $tokenExpiresAt = null;
        $exp = self::extractJwtExp($accessToken);
        if (is_int($exp) && $exp > 0) {
            $tokenExpiresAt = Carbon::createFromTimestampUTC($exp);
        } else {
            // fallback: 24h
            $tokenExpiresAt = now()->addDay();
        }

        // === 5) Persiste no banco ============================================
        $settings->access_token     = $accessToken;
        $settings->token_expires_at = $tokenExpiresAt;
        $settings->save();

        $this->command->info('GlobalSettings atualizado com token válido. Expira em: '.$tokenExpiresAt->toDateTimeString().'Z');
    }

    /**
     * Extrai a claim "exp" (timestamp) de um JWT sem validar a assinatura.
     * Retorna int|false.
     */
    private static function extractJwtExp(string $jwt)
    {
        try {
            $parts = explode('.', $jwt);
            if (count($parts) < 2) return false;

            $payloadB64 = $parts[1];

            // base64url decode
            $replaced = strtr($payloadB64, '-_', '+/');
            $padded = $replaced.Str::repeat('=', (4 - strlen($replaced) % 4) % 4);

            $payloadJson = base64_decode($padded, true);
            if ($payloadJson === false) return false;

            $payload = json_decode($payloadJson, true);
            if (!is_array($payload) || !isset($payload['exp'])) return false;

            return (int) $payload['exp'];
        } catch (\Throwable $e) {
            return false;
        }
    }
}
