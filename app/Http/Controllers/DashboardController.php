<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Nota; // ou App\Models\Nota se for esse o nome
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->hasRole('contas')) {
        $notasClinicas = Nota::clinicas()
            ->with('user') // eager loading do responsável
            ->orderByDesc('data_emissao')
            ->paginate(10, ['*'], 'clinicas_page');

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

            return view('dashboard', compact('notasPendentes', 'historicoNotas'));
        }

        if ($user->hasRole('chefia')) {
            $notasPendentes = Nota::whereNull('aprovado_chefia_em')
                ->orderBy('created_at', 'desc')
                ->paginate(10, ['*'], 'pendentes');

            $historicoNotas = Nota::whereNotNull('aprovado_chefia_em')
                ->orderBy('aprovado_chefia_em', 'desc')
                ->paginate(10, ['*'], 'historico');

            return view('dashboard', compact('notasPendentes', 'historicoNotas'));
        }


        return view('dashboard');
    }
}
