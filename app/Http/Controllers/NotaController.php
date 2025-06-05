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
    try {
        $tipoNota = $request->input('tipo_nota', 'clinica');
        
        if ($tipoNota === 'clinica') {
            return $this->storeClinica($request);
        } else {
            return $this->storeMedico($request);
        }
    } catch (\Exception $e) {
        \Log::error('Erro ao salvar nota', [
            'error' => $e->getMessage(),
            'data' => $request->all()
        ]);
        return back()->withInput()->with('error', 'Erro ao salvar: ' . $e->getMessage());
    }
}

protected function storeClinica(Request $request)
{
    $data = $request->validate([
        'tipo_nota' => 'required|in:clinica,medico',
        'numero_nf' => 'required|string|max:50',
        'prestador' => 'string|max:255',
        'cnpj' => 'nullable|string|max:18',
        'valor_total' => 'numeric|min:0',
        'data_emissao' => 'date',
        'vencimento_original' => 'date',
        'vencimento_prorrogado' => 'nullable|date',
        'data_entregue_financeiro' => 'nullable|date',
        'mes' => 'nullable|string|max:7',
        'tipo_pagamento' => 'nullable|in:boleto,deposito,pix',
        'dados_bancarios' => 'nullable|string',
        'taxa_correio' => 'nullable|boolean',
        'valor_taxa_correio' => 'nullable|numeric|min:0',
        'arquivo_nf' => 'nullable|file|mimes:pdf|max:5120',
        'clientes' => 'array|min:1',
        'clientes.*.cliente_atendido' => 'string|max:255',
        'clientes.*.valor' => 'numeric|min:0',
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
    // Validação mais robusta
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
        'med_horarios.*.saida_almoco' => 'nullable|date_format:H:i',
        'med_horarios.*.retorno_almoco' => 'nullable|date_format:H:i',
        'med_horarios.*.saida' => 'required|date_format:H:i',
        'med_horarios.*.valor_hora' => 'required|numeric|min:0',
        'med_horarios.*.total' => 'required|numeric|min:0',
        'med_deslocamento' => 'nullable|boolean',
        'med_valor_deslocamento' => 'nullable|required_if:med_deslocamento,1|numeric|min:0',
        'med_cobrou_almoco' => 'nullable|boolean',
        'med_valor_almoco' => 'nullable|required_if:med_cobrou_almoco,1|numeric|min:0',
        'med_reembolso_correios' => 'nullable|boolean',
        'med_valor_correios' => 'nullable|required_if:med_reembolso_correios,1|numeric|min:0',
        'med_valor_total_final' => 'required|numeric|min:0',
        'med_dados_bancarios' => 'nullable|string',
        'med_numero_nf' => 'required|string|max:255',
        'med_vencimento_original' => 'required|date',
        'med_mes' => 'nullable|string|max:50',
        'med_vencimento_prorrogado' => 'nullable|date',
        'med_tipo_pagamento' => 'nullable|string|max:100',
        'med_observacao' => 'nullable|string',
    ]);

    try {
        // Cálculo do valor total com base nos horários
        $totalCalculado = collect($validated['med_horarios'])->sum('total');
        
        // Verificação de consistência
        if (abs($totalCalculado - $validated['med_valor_total_final']) > 0.01) {
            throw new \Exception("O valor total calculado não corresponde ao valor informado");
        }

        // Preparação dos dados
        $data = [
            'tipo_nota' => $validated['tipo_nota'],
            'numero_nf' => $validated['med_numero_nf'],
            'vencimento_original' => $validated['med_vencimento_original'],
            'mes' => $validated['med_mes'] ?? null,
            'vencimento_prorrogado' => $validated['med_vencimento_prorrogado'] ?? null,
            'tipo_pagamento' => $validated['med_tipo_pagamento'] ?? null,
            'dados_bancarios' => $validated['med_dados_bancarios'] ?? null,
            'observacao' => $validated['med_observacao'] ?? null,
            
            // Campos específicos do médico
            'med_nome' => $validated['med_nome'],
            'med_telefone' => $validated['med_telefone'] ?? null,
            'med_email' => $validated['med_email'] ?? null,
            'med_cliente_atendido' => $validated['med_cliente_atendido'],
            'med_local' => $validated['med_local'] ?? null,
            'med_horarios' => json_encode($validated['med_horarios']),
            'med_valor_total_final' => $validated['med_valor_total_final'],
            'med_deslocamento' => $request->has('med_deslocamento'),
            'med_valor_deslocamento' => $request->has('med_deslocamento') ? $validated['med_valor_deslocamento'] : 0,
            'med_cobrou_almoco' => $request->has('med_cobrou_almoco'),
            'med_valor_almoco' => $request->has('med_cobrou_almoco') ? $validated['med_valor_almoco'] : 0,
            'med_reembolso_correios' => $request->has('med_reembolso_correios'),
            'med_valor_correios' => $request->has('med_reembolso_correios') ? $validated['med_valor_correios'] : 0,
            'user_id' => auth()->id(),
            'status' => 'lancada',
        ];

        // Log dos dados antes de salvar
        \Log::info('Tentativa de criar nota médica', ['data' => $data]);

        // Criação do registro
        $nota = Nota::create($data);

        return redirect()->route('dashboard')->with('success', 'Nota de médico cadastrada com sucesso!');

    } catch (\Exception $e) {
        \Log::error('Erro ao salvar nota médica', [
            'error' => $e->getMessage(),
            'data' => $request->all(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return back()->withInput()->withErrors([
            'error' => 'Ocorreu um erro ao salvar a nota. Detalhes foram registrados.'
        ]);
    }
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

    
    public function aprovar(Request $request, Nota $nota)
    {
        if ($nota->aprovado_chefia_em) {
            return redirect()->back()->with('error', 'Nota já aprovada ou rejeitada.');
        }

        $nota->update([
            'aprovado_chefia_em' => now(),
            'aprovado_chefia_por' => auth()->id(),
            'status' => 'confirmada_financeiro',
        ]);

        return redirect()->back()->with('success', 'Nota aprovada com sucesso!');
    }
    
    public function rejeitar(Request $request, Nota $nota)
    {
        $request->validate([
            'motivo_rejeicao' => 'required|string|min:10|max:500'
        ]);

        if ($nota->aprovado_chefia_em) {
            return redirect()->back()->with('error', 'Nota já aprovada ou rejeitada.');
        }

        $nota->update([
            'aprovado_chefia_em' => now(),
            'aprovado_chefia_por' => auth()->id(),
            'status' => 'rejeitada',
            'motivo_rejeicao_chefia' => $request->motivo_rejeicao
        ]);

        return redirect()->back()->with('success', 'Nota rejeitada com sucesso!');
    }

    public function aceitar(Request $request, Nota $nota)
    {
        $request->validate([
            'comprovante' => 'required|file|mimes:pdf,jpg,png|max:2048'
        ]);

        if ($nota->confirmado_financeiro_em) {
            return redirect()->back()->with('error', 'Nota já processada.');
        }

        // Upload do comprovante
        $comprovantePath = $request->file('comprovante')->store('comprovantes');

        $nota->update([
            'confirmado_financeiro_em' => now(),
            'confirmado_financeiro_por' => auth()->id(),
            'status' => 'finalizada',
            'comprovante_path' => $comprovantePath,
            'observacao_financeiro' => $request->observacao
        ]);

        return redirect()->back()->with('success', 'Nota finalizada com sucesso!');
    }

    public function recusar(Request $request, Nota $nota)
    {
        if ($nota->aprovada_financeiro_em) {
            return redirect()->back()->with('error', 'Nota já aprovada ou rejeitada.');
        }

        $nota->update([
            'confirmado_financeiro_em' => now(),
            'confirmado_financeiro_por' => auth()->id(),
            'status' => 'rejeitada',
        ]);

        return redirect()->back()->with('success', 'Nota rejeitada com sucesso!');
    }

    public function showComprovante(Nota $nota)
    {
        if (!$nota->comprovante_path) {
            abort(404);
        }

        return response()->file(storage_path('app/' . $nota->comprovante_path));
    }

}
