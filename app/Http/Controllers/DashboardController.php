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
            $notas = \App\Models\Nota::where('user_id', $user->id)->latest()->paginate(10);
            return view('dashboard', compact('notas'));
        }

        return view('dashboard');
    }
}
