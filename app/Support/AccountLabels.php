<?php

namespace App\Support;

class AccountLabels
{
    /**
     * Retorna o rótulo mapeado para o número da conta.
     * Se não existir, usa o fallback definido em config/account_labels.php.
     */
    public static function label(?string $account): string
    {
        $account = (string) ($account ?? '');
        $map = config('account_labels', []);
        if (!$account) {
            return is_callable($map['_default'] ?? null)
                ? ($map['_default'])('')
                : (string) ($map['_default'] ?? 'Sem rótulo');
        }

        if (isset($map[$account])) {
            return (string) $map[$account];
        }

        $fallback = $map['_default'] ?? 'Sem rótulo';
        return is_callable($fallback) ? $fallback($account) : (string) $fallback;
    }
}
