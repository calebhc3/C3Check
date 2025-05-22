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
        $tipoNota = $request->input('tipo_nota', 'clinica');
        
        if ($tipoNota === 'clinica') {
            return $this->storeClinica($request);
        } else {
            return $this->storeMedico($request);
        }
    }

    protected function storeClinica(Request $request)
    {
        $data = $request->validate([
            'tipo_nota' => 'required|in:clinica,medico',
            'numero_nf' => 'required|string|max:50',
            'prestador' => 'required|string|max:255',
            'cnpj' => 'nullable|string|max:18',
            'valor_total' => 'required|numeric|min:0',
            'data_emissao' => 'required|date',
            'vencimento_original' => 'required|date',
            'vencimento_prorrogado' => 'nullable|date',
            'data_entregue_financeiro' => 'nullable|date',
            'mes' => 'nullable|string|max:7',
            'tipo_pagamento' => 'nullable|in:boleto,deposito,pix',
            'dados_bancarios' => 'nullable|string',
            'taxa_correio' => 'nullable|boolean',
            'valor_taxa_correio' => 'nullable|numeric|min:0',
            'arquivo_nf' => 'nullable|file|mimes:pdf|max:5120',
            'clientes' => 'required|array|min:1',
            'clientes.*.cliente_atendido' => 'required|string|max:255',
            'clientes.*.valor' => 'required|numeric|min:0',
            'clientes.*.observacao' => 'nullable|string',
        ]);

        // Processar arquivo
        if ($request->hasFile('arquivo_nf')) {
            $data['arquivo_nf'] = $request->file('arquivo_nf')->store('notas', 'public');
        }

        // Converter clientes para JSON
        $data['clientes_atendidos'] = json_encode($data['clientes']);
        unset($data['clientes']);

        // Adicionar dados do usuário
        $data['user_id'] = auth()->id();
        $data['status'] = 'lancada';

        // Criar a nota
        $nota = Nota::create($data);

        return redirect()->route('notas.index')->with('success', 'Nota de clínica cadastrada com sucesso!');
    }

    protected function storeMedico(Request $request)
    {
        $data = $request->validate([
            'tipo_nota' => 'required|in:clinica,medico',
            'med_nome' => 'required|string|max:255',
            'med_telefone' => 'nullable|string|max:20',
            'med_email' => 'nullable|email|max:255',
            'med_cliente_atendido' => 'required|string|max:255',
            'med_local' => 'nullable|string|max:255',
            'med_horarios' => 'required|array|min:1',
            'med_horarios.*.data' => 'required|date',
            'med_horarios.*.horario' => 'required|date_format:H:i',
            'med_horarios.*.valor' => 'required|numeric|min:0',
            'med_deslocamento' => 'nullable|boolean',
            'med_valor_deslocamento' => 'nullable|numeric|min:0',
            'med_cobrou_almoco' => 'nullable|boolean',
            'med_valor_almoco' => 'nullable|numeric|min:0',
            'med_almoco_inicio' => 'nullable|date_format:H:i',
            'med_almoco_fim' => 'nullable|date_format:H:i',
            'med_reembolso_correios' => 'nullable|boolean',
            'med_valor_correios' => 'nullable|numeric|min:0',
            'med_valor_total_final' => 'required|numeric|min:0',
            'med_dados_bancarios' => 'nullable|string',
        ]);

        // Processar campos condicionais
        if (!$request->has('med_deslocamento')) {
            $data['med_valor_deslocamento'] = null;
        }
        
        if (!$request->has('med_cobrou_almoco')) {
            $data['med_valor_almoco'] = null;
            $data['med_almoco_inicio'] = null;
            $data['med_almoco_fim'] = null;
        }
        
        if (!$request->has('med_reembolso_correios')) {
            $data['med_valor_correios'] = null;
        }

        // Converter arrays para JSON
        $data['med_horarios'] = json_encode($data['med_horarios']);
        
        if (!empty($data['med_dados_bancarios'])) {
            $data['med_dados_bancarios'] = json_encode(['dados' => $data['med_dados_bancarios']]);
        }

        // Adicionar dados do usuário
        $data['user_id'] = auth()->id();
        $data['status'] = 'lancada';

        // Criar a nota
        $nota = Nota::create($data);

        return redirect()->route('notas.index')->with('success', 'Nota de médico cadastrada com sucesso!');
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
    public function aprovacaoIndex()
    {
        $notas = Nota::whereNull('aprovado_chefia_em')->orderBy('created_at', 'desc')->paginate(10);

        return view('chefia.notas.aprovacao', compact('notas'));
    }

    public function aprovar(Request $request, Nota $nota)
    {
        if ($nota->aprovado_chefia_em) {
            return redirect()->back()->with('error', 'Nota já aprovada ou rejeitada.');
        }

        $nota->update([
            'aprovado_chefia_em' => now(),
            'aprovado_chefia_por' => auth()->id(),
            'status' => 'aprovada_chefia',
        ]);

        return redirect()->back()->with('success', 'Nota aprovada com sucesso!');
    }

    public function rejeitar(Request $request, Nota $nota)
    {
        if ($nota->aprovado_chefia_em) {
            return redirect()->back()->with('error', 'Nota já aprovada ou rejeitada.');
        }

        $nota->update([
            'aprovado_chefia_em' => now(),
            'aprovado_chefia_por' => auth()->id(),
            'status' => 'rejeitada',
        ]);

        return redirect()->back()->with('success', 'Nota rejeitada com sucesso!');
    }

}
