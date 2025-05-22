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
            $notas = Nota::where('status', 'pendente')->with('user')->get();
            return view('dashboard', compact('notas'));
        }

        if ($user->hasRole('chefia')) {
            $notas = Nota::where('status', 'pendente')->with('user')->get();
            return view('dashboard', compact('notas'));
        }

        return view('dashboard');
    }
}
