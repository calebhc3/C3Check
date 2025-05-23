<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Nota;
use Illuminate\Support\Facades\Storage;
use App\Models\NotaCliente;

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

        return redirect()->route('dashboard')->with('success', 'Nota de clínica cadastrada com sucesso!');
    }

    protected function storeMedico(Request $request)
    {
        $validated = $request->validate([
            'tipo_nota' => 'required|in:clinica,medico',
            'med_nome' => 'required|string|max:255',
            'med_telefone' => 'nullable|string|max:20',
            'med_email' => 'nullable|email|max:255',
            'med_cliente_atendido' => 'required|string|max:255',
            'med_local' => 'nullable|string|max:255',
            'med_horarios' => 'required|array|min:1',
            'med_horarios.*.data' => 'required|date',
            'med_horarios.*.entrada' => 'required|date_format:H:i',
            'med_horarios.*.saida_almoco' => 'required|date_format:H:i',
            'med_horarios.*.retorno_almoco' => 'required|date_format:H:i',
            'med_horarios.*.saida' => 'required|date_format:H:i',
            'med_horarios.*.valor_hora' => 'required|numeric|min:0',
            'med_horarios.*.total' => 'required|numeric|min:0',
            'med_deslocamento' => 'nullable|boolean',
            'med_valor_deslocamento' => 'nullable|numeric|min:0',
            'med_cobrou_almoco' => 'nullable|boolean',
            'med_valor_almoco' => 'nullable|numeric|min:0',
            'med_almoco_inicio' => 'nullable|date_format:H:i',
            'med_almoco_fim' => 'nullable|date_format:H:i',
            'med_reembolso_correios' => 'nullable|boolean',
            'med_valor_correios' => 'nullable|numeric|min:0',
            'med_dados_bancarios' => 'nullable|string',
            // Campos que precisam ser "traduzidos"
            'med_numero_nf' => 'nullable|string|max:255',
            'med_vencimento_original' => 'nullable|date',
            'med_mes' => 'nullable|string|max:50',
            'med_vencimento_prorrogado' => 'nullable|date',
            'med_tipo_pagamento' => 'nullable|string|max:100',
        ]);

        // Cálculo do valor total com base nos horários
        $validated['med_valor_total_final'] = collect($validated['med_horarios'])->sum('total');

        // Conversões de JSON
        $validated['med_horarios'] = json_encode($validated['med_horarios']);

        // Checkbox normalization
        $validated['med_deslocamento'] = $request->has('med_deslocamento');
        $validated['med_cobrou_almoco'] = $request->has('med_cobrou_almoco');
        $validated['med_reembolso_correios'] = $request->has('med_reembolso_correios');

        // Reset de valores se não forem marcados
        if (!$validated['med_cobrou_almoco']) {
            $validated['med_valor_almoco'] = 0;
            $validated['med_almoco_inicio'] = null;
            $validated['med_almoco_fim'] = null;
        }

        if (!$validated['med_deslocamento']) {
            $validated['med_valor_deslocamento'] = 0;
        }

        if (!$validated['med_reembolso_correios']) {
            $validated['med_valor_correios'] = 0;
        }

        // Dados bancários em JSON se necessário
        if (!empty($validated['med_dados_bancarios'])) {
            $validated['med_dados_bancarios'] = json_encode(['dados' => $validated['med_dados_bancarios']]);
        }

    
        $mapaCampos = [
        'med_numero_nf' => 'numero_nf',
        'med_vencimento_original' => 'vencimento_original',
        'med_mes' => 'mes',
        'med_vencimento_prorrogado' => 'vencimento_prorrogado',
        'med_tipo_pagamento' => 'tipo_pagamento',
        'med_dados_bancarios' => 'dados_bancarios',
        'med_observacao' => 'observacao',
    ];

    // Começamos com todos os campos com prefixo "med_" que são exclusivos do modelo de médico
    $data = [
        'tipo_nota' => $validated['tipo_nota'],
        'med_nome' => $validated['med_nome'],
        'med_telefone' => $validated['med_telefone'] ?? null,
        'med_email' => $validated['med_email'] ?? null,
        'med_cliente_atendido' => $validated['med_cliente_atendido'],
        'med_local' => $validated['med_local'] ?? null,
        'med_horarios' => $validated['med_horarios'],
        'med_valor_total_final' => $validated['med_valor_total_final'],
        'med_deslocamento' => $validated['med_deslocamento'],
        'med_valor_deslocamento' => $validated['med_valor_deslocamento'] ?? null,
        'med_cobrou_almoco' => $validated['med_cobrou_almoco'],
        'med_valor_almoco' => $validated['med_valor_almoco'] ?? null,
        'med_almoco_inicio' => $validated['med_almoco_inicio'] ?? null,
        'med_almoco_fim' => $validated['med_almoco_fim'] ?? null,
        'med_reembolso_correios' => $validated['med_reembolso_correios'],
        'med_valor_correios' => $validated['med_valor_correios'] ?? null,
        'user_id' => auth()->id(),
        'status' => 'lancada',
    ];

    // Adicionamos apenas os 6 campos "traduzidos"
    foreach ($mapaCampos as $medCampo => $campoReal) {
        if (isset($validated[$medCampo])) {
            $data[$campoReal] = $validated[$medCampo];
        }
    }
        Nota::create($data);

        return redirect()->route('dashboard')->with('success', 'Nota de médico cadastrada com sucesso!');
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
        $nota->load('notaClientes');
        return view('notas.edit', compact('nota'));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Nota $nota)
    {
        $tipoNota = $request->input('tipo_nota', 'clinica');

        if ($tipoNota === 'clinica') {
            return $this->updateClinica($request, $nota);
        } else {
            return $this->updateMedico($request, $nota);
        }
    }

protected function updateClinica(Request $request, Nota $nota)
{

    $data = $request->validate([
        'tipo_nota' => 'required|in:clinica,medico',
        'numero_nf' => 'required|string|max:50',
        'prestador' => 'required|string|max:255',
        'cnpj' => 'nullable|string|max:18',
        'valor_total' => 'required|numeric|min:0',
        'vencimento_original' => 'required|date',
        'vencimento_prorrogado' => 'nullable|date',
        'data_entregue_financeiro' => 'nullable|date',
        'mes' => 'nullable|string|max:7',
        'tipo_pagamento' => 'nullable|in:boleto,deposito,pix',
        'dados_bancarios' => 'nullable|string',
        'taxa_correio' => 'sometimes|accepted', // Modificado para aceitar checkbox
        'valor_taxa_correio' => 'nullable|numeric|min:0',
        'arquivo_nf' => 'nullable|file|mimes:pdf|max:5120',
        'clientes' => 'required|array|min:1',
        'clientes.*.cliente_atendido' => 'required|string|max:255',
        'clientes.*.valor' => 'required|numeric|min:0',
        'clientes.*.observacao' => 'nullable|string',
        'observacao' => 'nullable|string',
    ]);

    // Converter checkbox para boolean
    $data['taxa_correio'] = $request->has('taxa_correio');

    // Processar arquivo
    if ($request->hasFile('arquivo_nf')) {
        Storage::disk('public')->delete($nota->arquivo_nf);
        $data['arquivo_nf'] = $request->file('arquivo_nf')->store('notas', 'public');
    }

    // Processar clientes
    $nota->notaClientes()->delete();
    foreach ($request->clientes as $clienteData) {
        $nota->notaClientes()->create($clienteData);
    }

    // Atualizar a nota
    $nota->update($data);

    return redirect()->route('dashboard')->with('success', 'Nota atualizada com sucesso!');
}
    protected function updateMedico(Request $request, Nota $nota)
    {
        $validated = $request->validate([
            'tipo_nota' => 'required|in:clinica,medico',
            'med_nome' => 'required|string|max:255',
            'med_telefone' => 'nullable|string|max:20',
            'med_email' => 'nullable|email|max:255',
            'med_cliente_atendido' => 'required|string|max:255',
            'med_local' => 'nullable|string|max:255',
            'med_horarios' => 'required|array|min:1',
            'med_horarios.*.data' => 'required|date',
            'med_horarios.*.entrada' => 'required|date_format:H:i',
            'med_horarios.*.saida_almoco' => 'required|date_format:H:i',
            'med_horarios.*.retorno_almoco' => 'required|date_format:H:i',
            'med_horarios.*.saida' => 'required|date_format:H:i',
            'med_horarios.*.valor_hora' => 'required|numeric|min:0',
            'med_horarios.*.total' => 'required|numeric|min:0',
            'med_deslocamento' => 'nullable|boolean',
            'med_valor_deslocamento' => 'nullable|numeric|min:0',
            'med_cobrou_almoco' => 'nullable|boolean',
            'med_valor_almoco' => 'nullable|numeric|min:0',
            'med_almoco_inicio' => 'nullable|date_format:H:i',
            'med_almoco_fim' => 'nullable|date_format:H:i',
            'med_reembolso_correios' => 'nullable|boolean',
            'med_valor_correios' => 'nullable|numeric|min:0',
            'med_dados_bancarios' => 'nullable|string',
            'med_numero_nf' => 'nullable|string|max:255',
            'med_vencimento_original' => 'nullable|date',
            'med_mes' => 'nullable|string|max:50',
            'med_vencimento_prorrogado' => 'nullable|date',
            'med_tipo_pagamento' => 'nullable|string|max:100',
        ]);

        $validated['med_valor_total_final'] = collect($validated['med_horarios'])->sum('total');
        $validated['med_horarios'] = json_encode($validated['med_horarios']);

        $validated['med_deslocamento'] = $request->has('med_deslocamento');
        $validated['med_cobrou_almoco'] = $request->has('med_cobrou_almoco');
        $validated['med_reembolso_correios'] = $request->has('med_reembolso_correios');

        if (!$validated['med_cobrou_almoco']) {
            $validated['med_valor_almoco'] = 0;
            $validated['med_almoco_inicio'] = null;
            $validated['med_almoco_fim'] = null;
        }

        if (!$validated['med_deslocamento']) {
            $validated['med_valor_deslocamento'] = 0;
        }

        if (!$validated['med_reembolso_correios']) {
            $validated['med_valor_correios'] = 0;
        }

        if (!empty($validated['med_dados_bancarios'])) {
            $validated['med_dados_bancarios'] = json_encode(['dados' => $validated['med_dados_bancarios']]);
        }

        $mapaCampos = [
            'med_numero_nf' => 'numero_nf',
            'med_vencimento_original' => 'vencimento_original',
            'med_mes' => 'mes',
            'med_vencimento_prorrogado' => 'vencimento_prorrogado',
            'med_tipo_pagamento' => 'tipo_pagamento',
            'med_dados_bancarios' => 'dados_bancarios',
            'med_observacao' => 'observacao',
        ];

        $data = [
            'tipo_nota' => $validated['tipo_nota'],
            'med_nome' => $validated['med_nome'],
            'med_telefone' => $validated['med_telefone'] ?? null,
            'med_email' => $validated['med_email'] ?? null,
            'med_cliente_atendido' => $validated['med_cliente_atendido'],
            'med_local' => $validated['med_local'] ?? null,
            'med_horarios' => $validated['med_horarios'],
            'med_valor_total_final' => $validated['med_valor_total_final'],
            'med_deslocamento' => $validated['med_deslocamento'],
            'med_valor_deslocamento' => $validated['med_valor_deslocamento'] ?? null,
            'med_cobrou_almoco' => $validated['med_cobrou_almoco'],
            'med_valor_almoco' => $validated['med_valor_almoco'] ?? null,
            'med_almoco_inicio' => $validated['med_almoco_inicio'] ?? null,
            'med_almoco_fim' => $validated['med_almoco_fim'] ?? null,
            'med_reembolso_correios' => $validated['med_reembolso_correios'],
            'med_valor_correios' => $validated['med_valor_correios'] ?? null,
            'user_id' => auth()->id(),
            'status' => 'lancada',
        ];

        foreach ($mapaCampos as $medCampo => $campoReal) {
            if (isset($validated[$medCampo])) {
                $data[$campoReal] = $validated[$medCampo];
            }
        }

        $nota->update($data);

        return redirect()->route('dashboard')->with('success', 'Nota de médico atualizada com sucesso!');
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
