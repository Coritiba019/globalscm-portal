<?php

namespace App\Http\Controllers;

use App\Services\GlobalScmClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SelectAccountController extends Controller
{
    public function index(Request $request, GlobalScmClient $client)
    {
        // Busca contas da empresa (ajuste paginação se quiser)
        $resp = $client->listAccounts(page: 1, limit: 50);
        $accounts = $resp['items'] ?? [];

        $current = (int) ($request->session()->get('digital_account_id') ?? 0);

        return view('accounts.select', compact('accounts','current'));
    }

    public function activate(Request $request)
    {
        $request->validate([
            'digital_account_id' => ['required','integer','min:1'],
        ]);

        $digital = (int) $request->input('digital_account_id');

        // Salva na sessão
        $request->session()->put('digital_account_id', $digital);

        // (Opcional) persistir como padrão do usuário
        if ($user = Auth::user()) {
            // se tiver coluna no users (ex.: default_digital_account_id), descomente:
            // $user->default_digital_account_id = $digital;
            // $user->save();
        }

        return redirect()->route('dashboard')
            ->with('status', 'Conta ativada com sucesso.');
    }
}
