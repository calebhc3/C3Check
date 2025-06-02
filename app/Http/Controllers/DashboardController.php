<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Nota; // ou App\Models\Nota se for esse o nome
use Illuminate\Http\Request;

class DashboardController extends Controller
{
public function index(Request $request)
{
    $user = Auth::user();

    // Garante que as variáveis existem mesmo se não forem usadas
    $notasClinicas = collect();
    $notasMedicos = collect();
    $notasPendentes = collect();
    $historicoNotas = collect();

    if ($user->hasRole('contas')) {
        $queryClinicas = Nota::clinicas()
            ->with('user')
            ->orderByDesc('data_emissao');

        if ($request->filled('cnpj')) {
            $queryClinicas->where('cnpj', 'like', '%' . $request->cnpj . '%');
        }

        if ($request->filled('numero_nf')) {
            $queryClinicas->where('numero_nf', 'like', '%' . $request->numero_nf . '%');
        }

        if ($request->filled('status')) {
            $queryClinicas->where('status', $request->status);
        }

        $notasClinicas = $queryClinicas->paginate(10, ['*'], 'clinicas_page')->withQueryString();

        $notasMedicos = Nota::medicos()
            ->with('user')
            ->orderByDesc('data_emissao')
            ->paginate(10, ['*'], 'medicos_page');

        return view('dashboard', compact('notasClinicas', 'notasMedicos'));
    }

    if ($user->hasRole('financeiro')) {
        $notasPendentes = Nota::whereNotNull('aprovado_chefia_em')
            ->whereNull('confirmado_financeiro_em')
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'pendentes');

        $historicoNotas = Nota::whereNotNull('confirmado_financeiro_em')
            ->orderBy('confirmado_financeiro_em', 'desc')
            ->paginate(10, ['*'], 'historico');

        return view('dashboard', compact('notasPendentes', 'historicoNotas', 'notasClinicas', 'notasMedicos'));
    }

    if ($user->hasRole('chefia')) {
        $notasPendentes = Nota::whereNull('aprovado_chefia_em')
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'pendentes');

        $historicoNotas = Nota::whereNotNull('aprovado_chefia_em')
            ->orderBy('aprovado_chefia_em', 'desc')
            ->paginate(10, ['*'], 'historico');

        return view('dashboard', compact('notasPendentes', 'historicoNotas', 'notasClinicas', 'notasMedicos'));
    }

    return view('dashboard', compact('notasClinicas', 'notasMedicos'));
}

}
