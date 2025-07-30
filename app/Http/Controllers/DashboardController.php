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

    // Inicializa coleções vazias
    $notasClinicas = collect();
    $notasMedicos = collect();
    $notasPrestadores = collect();
    $notasPendentes = collect();
    $historicoNotas = collect();

    // CONTAS
    if ($user->hasRole('contas')) {
        // CLÍNICAS
        $queryClinicas = Nota::clinicas()
            ->with('user')
            ->orderByDesc('data_emissao');

        // FILTROS CLÍNICAS
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

        // MÉDICOS
        $notasMedicos = Nota::medicos()
            ->with('user')
            ->orderByDesc('data_emissao')
            ->paginate(10, ['*'], 'medicos_page');

        // PRESTADORES
        $notasPrestadores = Nota::where('tipo_nota', 'prestador')
            ->with('user')
            ->orderByDesc('data_emissao')
            ->paginate(10, ['*'], 'prestadores_page');

        return view('dashboard', compact('notasClinicas', 'notasMedicos', 'notasPrestadores'));
    }

    // FINANCEIRO
    if ($user->hasRole('financeiro')) {
        $notasPendentes = Nota::where('status', 'aprovada_chefia')
            ->whereNull('confirmado_financeiro_em')
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'pendentes');

        $historicoNotas = Nota::whereNotNull('confirmado_financeiro_em')
            ->orderBy('confirmado_financeiro_em', 'desc')
            ->paginate(10, ['*'], 'historico');

        return view('dashboard', compact(
            'notasPendentes',
            'historicoNotas',
            'notasClinicas',
            'notasMedicos',
            'notasPrestadores'
        ));
    }

    // CHEFIA
    if ($user->hasRole('chefia')) {
        $notasPendentes = Nota::where('status', 'lancada')
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'pendentes');

        $historicoNotas = Nota::whereNotNull('aprovado_chefia_em')
            ->orderBy('aprovado_chefia_em', 'desc')
            ->paginate(10, ['*'], 'historico');

        return view('dashboard', compact(
            'notasPendentes',
            'historicoNotas',
            'notasClinicas',
            'notasMedicos',
            'notasPrestadores'
        ));
    }

    // DEFAULT
    return view('dashboard', compact('notasClinicas', 'notasMedicos', 'notasPrestadores'));
}


}
