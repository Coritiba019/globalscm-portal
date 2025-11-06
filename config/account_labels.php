<?php

return [
    // Mapeamentos fornecidos
    '00100001675' => 'Nexus',
    '00100001672' => 'Pagopay',
    '00100001679' => 'Vitalcred',
    '00100001676' => 'King',
    '00100001697' => 'Will',
    '00100001686' => 'D Group',
    '00100001685' => 'Lumina',
    '00100001681' => 'Primax',
    '00100001651' => 'Univepay',
    '00100001689' => 'Rasp/Veopag',
    '00100001684' => 'Santos Lima',

    // Fallback: pode ser uma string fixa ou uma função que
    // receba o número da conta e devolva um texto padrão.
    '_default' => static function (string $account) {
        // Ex.: "Conta - 1697" ou "Sem rótulo"
        $suf = $account ? substr($account, -4) : '—';
        return "Conta · $suf";
    },
];
