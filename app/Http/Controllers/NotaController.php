<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Nota;

class NotaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $notas = Nota::where('user_id', auth()->id())->latest()->get();
        return view('notas.index', compact('notas'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('notas.create');
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'numero_nf' => 'required|string',
            'prestador' => 'required|string',
            'valor_total' => 'required|numeric|min:0',
            'data_emissao' => 'nullable|date',
            'vencimento_original' => 'nullable|date',
            'vencimento_prorrogado' => 'nullable|date',
            'tipo_pagamento' => 'nullable|in:boleto,deposito,pix',
            'arquivo_nf' => 'nullable|file|mimes:pdf|max:5120',
        ]);

        if ($request->hasFile('arquivo_nf')) {
            $data['arquivo_nf'] = $request->file('arquivo_nf')->store('notas', 'public');
        }

        $data['user_id'] = auth()->id();
        Nota::create($data);

        return redirect()->route('notas.index')->with('success', 'Nota cadastrada com sucesso!');
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Nota $nota)
    {
        $this->authorizeNota($nota);
        $nota->load('clientes');
        return view('notas.edit', compact('nota'));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Nota $nota)
    {
        $this->authorizeNota($nota);

        if ($nota->status !== 'lancada') {
            abort(403, 'Nota não pode mais ser editada.');
        }

        $data = $request->validate([
            'numero_nf' => 'required|string',
            'prestador' => 'required|string',
            'valor_total' => 'required|numeric|min:0',
            'data_emissao' => 'nullable|date',
            'vencimento_original' => 'nullable|date',
            'vencimento_prorrogado' => 'nullable|date',
            'tipo_pagamento' => 'nullable|in:boleto,deposito,pix',
            'arquivo_nf' => 'nullable|file|mimes:pdf|max:5120',
        ]);

        if ($request->hasFile('arquivo_nf')) {
            $data['arquivo_nf'] = $request->file('arquivo_nf')->store('notas', 'public');
        }

        $nota->update($data);

        return redirect()->route('notas.index')->with('success', 'Nota atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Nota $nota)
    {
        $this->authorizeNota($nota);

        if ($nota->status !== 'lancada') {
            abort(403, 'Nota não pode mais ser removida.');
        }

        $nota->delete();
        return redirect()->route('dashboard')->with('success', 'Nota removida.');
    }

    protected function authorizeNota(Nota $nota)
    {
        if ($nota->user_id !== auth()->id()) {
            abort(403, 'Acesso negado.');
        }
    }

}
