<?php

namespace App\Http\Controllers;

use App\Services\GlobalScmClient;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class ReportsController extends Controller
{
    public function dailyTransactions(Request $request, GlobalScmClient $client)
    {
        // Defaults para evitar "Undefined variable $type"
        $digital = (string) $request->query('digital', '');
        $type    = (string) $request->query('type', 'todos'); // 'todos' | 'credit' | 'debit'
        $subType = $request->query('subType'); // string|null
        $status  = $request->query('status');  // string|null

        // Filtros de data opcionais
        $initial = $request->query('initial'); // YYYY-MM-DD
        $final   = $request->query('final');   // YYYY-MM-DD

        $payload = ['digitalAccountId' => $digital];
        if ($type && $type !== 'todos') $payload['type'] = $type;
        if ($subType) $payload['subType'] = $subType;
        if ($status)  $payload['status']  = $status;
        if ($initial) $payload['initialDate'] = $initial;
        if ($final)   $payload['finalDate']   = $final;

        $resp   = $client->dailyReport($payload);
        $data   = Arr::get($resp, 'data', []);
        $sum    = Arr::get($resp, 'summary', []);
        $filters= Arr::get($resp, 'filters', []);

        $tz = 'America/Sao_Paulo';
        $rows = [];
        $labels = [];
        $values = [];

        foreach ($data as $row) {
            $dt = Carbon::parse(Arr::get($row, 'dia'))->timezone($tz);
            $total = (int) Arr::get($row, 'total_transacoes', 0);
            $rows[] = [
                'dia_br' => $dt->format('d/m/Y'),
                'total'  => $total,
            ];
            $labels[] = $dt->format('d/m');
            $values[] = $total;
        }

        $summary = [
            'totalDays' => (int) Arr::get($sum, 'totalDays', count($rows)),
            'totalTransactions' => (int) Arr::get($sum, 'totalTransactions', array_sum(array_column($rows, 'total'))),
        ];

        return view('reports.daily-transactions', [
            'digital' => $digital,
            'type'    => $type,
            'subType' => $subType,
            'status'  => $status,
            'initial' => $initial,
            'final'   => $final,
            'filters' => $filters ?: ['type'=>$type,'subType'=>$subType,'status'=>$status,'digital'=>$digital],
            'rows'    => $rows,
            'labels'  => $labels,
            'values'  => $values,
            'summary' => $summary,
        ]);
    }
}
