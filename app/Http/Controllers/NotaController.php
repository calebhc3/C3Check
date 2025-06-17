<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Nota;
use Illuminate\Support\Facades\Storage;
use App\Models\NotaCliente;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
    // Sobrescreve configurações PHP temporariamente para esta requisição
    ini_set('memory_limit', '256M');
    ini_set('upload_max_filesize', '100M');
    ini_set('post_max_size', '100M');

    // Validação MANUAL do arquivo (contorna limitações do Validator)
    if ($request->hasFile('arquivo_nf')) {
        $file = $request->file('arquivo_nf')[0];
        if ($file->getSize() > 100 * 1024 * 1024) { // 100MB
            return back()->withErrors(['arquivo_nf' => 'Arquivo excede 100MB']);
        }
        if ($file->getClientOriginalExtension() !== 'pdf') {
            return back()->withErrors(['arquivo_nf' => 'Apenas PDFs são aceitos']);
        }
    }
    // Converter checkbox para booleano antes da validação
    $request->merge([
        'taxa_correio' => $request->has('taxa_correio'),
        'glosar' => $request->input('glosar', '0') === '1' // Corrige para radio button
    ]);

    // Validação dos dados com mensagens personalizadas
    $validatedData = $request->validate([
        'tipo_nota' => 'required|in:clinica,medico',
        'numero_nf' => 'nullable|string|max:50',
        'prestador' => 'nullable|string|max:255',
        'cnpj' => 'nullable|string|max:18',
        'valor_total' => 'required|numeric|min:0.01',
        'vencimento_original' => 'nullable|date',
        'vencimento_prorrogado' => 'nullable|date|after_or_equal:vencimento_original',
        'mes' => 'nullable|string|max:7|regex:/^\d{2}\/\d{4}$/',
        'tipo_pagamento' => 'nullable|in:boleto,deposito,pix',
        'dados_bancarios' => 'nullable|string|max:500',
        'taxa_correio' => 'sometimes|boolean',
        'valor_taxa_correio' => 'nullable|numeric|min:0|required_if:taxa_correio,true',
        'arquivo_nf' => 'sometimes|array',
        'arquivo_nf.*' => 'sometimes|file|mimes:pdf|max:102400',
        'clientes' => 'required|array|min:1',
        'clientes.*.cliente_atendido' => 'required|string|max:255',
        'clientes.*.valor' => 'required|numeric|min:0.01',
        'clientes.*.observacao' => 'nullable|string|max:500',
        'glosar' => 'sometimes|boolean',
        'glosa_valor' => 'nullable|numeric|min:0|required_if:glosar,true',
        'glosa_motivo' => 'nullable|string|max:500|required_if:glosar,true',
        'cidade' => 'nullable|string|max:255',
        'estado' => 'nullable|string|max:255',
        'regiao' => 'nullable|in:Norte,Nordeste,Centro-Oeste,Sudeste,Sul'
    ], [
        'required' => 'O campo :attribute é obrigatório.',
        'clientes.*.cliente_atendido.required' => 'O nome do cliente é obrigatório.',
        'clientes.*.valor.required' => 'O valor do cliente é obrigatório.',
        'required_if' => 'O campo :attribute é obrigatório quando :other está ativo.',
        'vencimento_prorrogado.after_or_equal' => 'A data prorrogada deve ser igual ou posterior à data original.',
        'glosa_valor.required_if' => 'O campo valor da glosa é obrigatório quando a glosa está ativa.',
        'glosa_motivo.required_if' => 'O campo motivo da glosa é obrigatório quando a glosa está ativa.'
    ]);

    // Processar arquivos (se existirem)
    $caminhosArquivos = [];
    if ($request->hasFile('arquivo_nf')) {
        $ano = now()->format('Y');
        $mes = now()->format('m');

        foreach ($request->file('arquivo_nf') as $arquivo) {
            $fileName = 'NF_' . ($validatedData['numero_nf'] ?? 'sem_numero') . '_' . time() . '_' . uniqid() . '.pdf';
            $path = $arquivo->storeAs("notas/$ano/$mes", $fileName, 'public');
            $caminhosArquivos[] = $path;
        }
    }

    // Preparar dados para criação
    $notaData = [
        'tipo_nota' => $validatedData['tipo_nota'],
        'numero_nf' => $validatedData['numero_nf'] ?? null,
        'prestador' => $validatedData['prestador'] ?? null,
        'cnpj' => $validatedData['cnpj'] ?? null,
        'valor_total' => $validatedData['valor_total'],
        'data_emissao' => now(),
        'data_entregue_financeiro' => now(),
        'vencimento_original' => $validatedData['vencimento_original'] ?? null,
        'vencimento_prorrogado' => $validatedData['vencimento_prorrogado'] ?? null,
        'mes' => $validatedData['mes'] ?? null,
        'tipo_pagamento' => $validatedData['tipo_pagamento'] ?? null,
        'dados_bancarios' => $validatedData['dados_bancarios'] ?? null,
        'taxa_correio' => $validatedData['taxa_correio'] ?? false,
        'valor_taxa_correio' => $validatedData['taxa_correio'] ? ($validatedData['valor_taxa_correio'] ?? 0) : 0,
        'arquivo_nf' => !empty($caminhosArquivos) ? json_encode($caminhosArquivos) : null,
        'user_id' => auth()->id(),
        'status' => 'lancada',
        'glosar' => $validatedData['glosar'] ?? false,
        'glosa_valor' => ($validatedData['glosar'] ?? false) ? ($validatedData['glosa_valor'] ?? 0) : 0,
        'glosa_motivo' => ($validatedData['glosar'] ?? false) ? ($validatedData['glosa_motivo'] ?? null) : null,
        'cidade' => $validatedData['cidade'] ?? null,
        'estado' => $validatedData['estado'] ?? null,
        'regiao' => $validatedData['regiao'] ?? null,
        'aprovado_chefia_em' => null,
    ];

    // Criar a nota em uma transação para garantir integridade
    DB::beginTransaction();

    try {
        $nota = Nota::create($notaData);

        // Criar clientes associados
        foreach ($validatedData['clientes'] as $cliente) {
            $nota->notaClientes()->create([
                'cliente_atendido' => $cliente['cliente_atendido'],
                'valor' => $cliente['valor'],
                'observacao' => $cliente['observacao'] ?? null,
            ]);
        }

        DB::commit();

        return redirect()
            ->route('dashboard')
            ->with('success', 'Nota de clínica cadastrada com sucesso!');

    } catch (\Exception $e) {
        DB::rollBack();
        
        // Excluir arquivos enviados em caso de erro
        foreach ($caminhosArquivos as $arquivo) {
            Storage::disk('public')->delete($arquivo);
        }

        return back()
            ->withInput()
            ->with('error', 'Ocorreu um erro ao salvar a nota: ' . $e->getMessage());
    }
}

protected function storeMedico(Request $request)
{
    // Validação mais robusta
    $validated = $request->validate([
        'tipo_nota' => 'in:clinica,medico',
        'med_nome' => 'string|max:255',
        'med_telefone' => 'nullable|string|max:20',
        'med_email' => 'nullable|email|max:255',
        'med_cliente_atendido' => 'string|max:255',
        'med_local' => 'nullable|string|max:255',
        'med_horarios' => 'array|min:1',
        'med_horarios.*.data' => 'date',
        'med_horarios.*.entrada' => 'date_format:H:i',
        'med_horarios.*.saida_almoco' => 'nullable|date_format:H:i',
        'med_horarios.*.retorno_almoco' => 'nullable|date_format:H:i',
        'med_horarios.*.saida' => 'date_format:H:i',
        'med_horarios.*.valor_hora' => 'numeric|min:0',
        'med_horarios.*.total' => 'numeric|min:0',
        'med_deslocamento' => 'nullable|boolean',
        'med_valor_deslocamento' => 'nullable|required_if:med_deslocamento,1|numeric|min:0',
        'med_cobrou_almoco' => 'nullable|boolean',
        'med_valor_almoco' => 'nullable|required_if:med_cobrou_almoco,1|numeric|min:0',
        'med_reembolso_correios' => 'nullable|boolean',
        'med_valor_correios' => 'nullable|required_if:med_reembolso_correios,1|numeric|min:0',
        'med_valor_total_final' => 'numeric|min:0',
        'med_dados_bancarios' => 'nullable|string',
        'med_numero_nf' => 'string|max:255',
        'med_vencimento_original' => 'date',
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
        $data['glosa_valor'] = $request->boolean('glosar') ? $request->input('glosa_valor') : 0;
        $data['glosa_motivo'] = $request->boolean('glosar') ? $request->input('glosa_motivo') : null;

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

    public function detalhes($id)
    {
        $nota = Nota::with(['notaclientes'])->findOrFail($id);

        // Decide qual view parcial retornar com base no tipo
        if ($nota->tipo_nota === 'clinica') {
            return view('notas.partials.detalhes-clinica', compact('nota'));
        }

        if ($nota->tipo_nota === 'medico') {
            return view('notas.partials.detalhes-medico', compact('nota'));
        }

        return response()->json(['erro' => 'Tipo de nota inválido.'], 422);
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
    // Sobrescreve configurações PHP temporariamente para esta requisição
    ini_set('memory_limit', '256M');
    ini_set('upload_max_filesize', '100M');
    ini_set('post_max_size', '100M');

    // Validação MANUAL do arquivo (igual ao store)
    if ($request->hasFile('arquivo_nf')) {
        $file = $request->file('arquivo_nf')[0];
        if ($file->getSize() > 100 * 1024 * 1024) {
            return back()->withErrors(['arquivo_nf' => 'Arquivo excede 100MB']);
        }
        if ($file->getClientOriginalExtension() !== 'pdf') {
            return back()->withErrors(['arquivo_nf' => 'Apenas PDFs são aceitos']);
        }
    }

    // Converter checkboxes (igual ao store)
    $request->merge([
        'taxa_correio' => $request->has('taxa_correio'),
        'glosar' => $request->input('glosar', '0') === '1'
    ]);

    // Validação consistente com o store
    $validatedData = $request->validate([
        'tipo_nota' => 'required|in:clinica,medico',
        'numero_nf' => 'nullable|string|max:50',
        'prestador' => 'nullable|string|max:255',
        'cnpj' => 'nullable|string|max:18',
        'valor_total' => 'required|numeric|min:0.01',
        'vencimento_original' => 'nullable|date',
        'vencimento_prorrogado' => 'nullable|date|after_or_equal:vencimento_original',
        'mes' => 'nullable|string|max:7|regex:/^\d{2}\/\d{4}$/',
        'tipo_pagamento' => 'nullable|in:boleto,deposito,pix',
        'dados_bancarios' => 'nullable|string|max:500',
        'taxa_correio' => 'sometimes|boolean',
        'valor_taxa_correio' => 'nullable|numeric|min:0|required_if:taxa_correio,true',
        'arquivo_nf' => 'sometimes|array',
        'arquivo_nf.*' => 'sometimes|file|mimes:pdf|max:102400',
        'clientes' => 'required|array|min:1',
        'clientes.*.cliente_atendido' => 'required|string|max:255',
        'clientes.*.valor' => 'required|numeric|min:0.01',
        'clientes.*.observacao' => 'nullable|string|max:500',
        'glosar' => 'sometimes|boolean',
        'glosa_valor' => 'nullable|numeric|min:0|required_if:glosar,true',
        'glosa_motivo' => 'nullable|string|max:500|required_if:glosar,true',
    ], [
        // Mesmas mensagens personalizadas
        'required' => 'O campo :attribute é obrigatório.',
        'clientes.*.cliente_atendido.required' => 'O nome do cliente é obrigatório.',
        'clientes.*.valor.required' => 'O valor do cliente é obrigatório.',
        'required_if' => 'O campo :attribute é obrigatório quando :other está ativo.',
        'vencimento_prorrogado.after_or_equal' => 'A data prorrogada deve ser igual ou posterior à data original.',
        'glosa_valor.required_if' => 'O campo valor da glosa é obrigatório quando a glosa está ativa.',
        'glosa_motivo.required_if' => 'O campo motivo da glosa é obrigatório quando a glosa está ativa.'
    ]);

    // Processar arquivos (modelo melhorado)
    DB::beginTransaction();

    try {
        $caminhosArquivos = [];
        
        if ($request->hasFile('arquivo_nf')) {
            // Remove arquivos antigos
            if ($nota->arquivo_nf) {
                $arquivosAntigos = json_decode($nota->arquivo_nf, true);
                foreach ($arquivosAntigos as $arquivo) {
                    Storage::disk('public')->delete($arquivo);
                }
            }

            // Salva novos arquivos
            $ano = now()->format('Y');
            $mes = now()->format('m');
            
            foreach ($request->file('arquivo_nf') as $arquivo) {
                $fileName = 'NF_' . ($validatedData['numero_nf'] ?? 'sem_numero') . '_' . time() . '_' . uniqid() . '.pdf';
                $path = $arquivo->storeAs("notas/$ano/$mes", $fileName, 'public');
                $caminhosArquivos[] = $path;
            }
        }

        // Atualiza dados da nota
        $nota->update([
            'tipo_nota' => $validatedData['tipo_nota'],
            'numero_nf' => $validatedData['numero_nf'] ?? null,
            'prestador' => $validatedData['prestador'] ?? null,
            'cnpj' => $validatedData['cnpj'] ?? null,
            'valor_total' => $validatedData['valor_total'],
            'vencimento_original' => $validatedData['vencimento_original'] ?? null,
            'vencimento_prorrogado' => $validatedData['vencimento_prorrogado'] ?? null,
            'mes' => $validatedData['mes'] ?? null,
            'tipo_pagamento' => $validatedData['tipo_pagamento'] ?? null,
            'dados_bancarios' => $validatedData['dados_bancarios'] ?? null,
            'taxa_correio' => $validatedData['taxa_correio'] ?? false,
            'valor_taxa_correio' => $validatedData['taxa_correio'] ? ($validatedData['valor_taxa_correio'] ?? 0) : 0,
            'arquivo_nf' => !empty($caminhosArquivos) ? json_encode($caminhosArquivos) : $nota->arquivo_nf,
            'glosar' => $validatedData['glosar'] ?? false,
            'glosa_valor' => ($validatedData['glosar'] ?? false) ? ($validatedData['glosa_valor'] ?? 0) : 0,
            'glosa_motivo' => ($validatedData['glosar'] ?? false) ? ($validatedData['glosa_motivo'] ?? null) : null,
        ]);

        // Atualiza clientes
        $nota->notaClientes()->delete();
        foreach ($validatedData['clientes'] as $cliente) {
            $nota->notaClientes()->create([
                'cliente_atendido' => $cliente['cliente_atendido'],
                'valor' => $cliente['valor'],
                'observacao' => $cliente['observacao'] ?? null,
            ]);
        }

        // Atualiza status se necessário
        if ($nota->status === 'rejeitada') {
            $nota->update(['status' => 'lancada']);
        }

        DB::commit();

        return redirect()
            ->route('dashboard')
            ->with('success', 'Nota atualizada com sucesso!');

    } catch (\Exception $e) {
        DB::rollBack();
        
        // Remove arquivos novos em caso de erro
        if (!empty($caminhosArquivos)) {
            foreach ($caminhosArquivos as $arquivo) {
                Storage::disk('public')->delete($arquivo);
            }
        }

        return back()
            ->withInput()
            ->with('error', 'Ocorreu um erro ao atualizar a nota: ' . $e->getMessage());
    }
}

    protected function updateMedico(Request $request, Nota $nota)
    {
        $validated = $request->validate([
            'tipo_nota' => 'in:clinica,medico',
            'med_nome' => 'string|max:255',
            'med_telefone' => 'nullable|string|max:20',
            'med_email' => 'nullable|email|max:255',
            'med_cliente_atendido' => 'string|max:255',
            'med_local' => 'nullable|string|max:255',
            'med_horarios' => 'array|min:1',
            'med_horarios.*.data' => 'date',
            'med_horarios.*.entrada' => 'date_format:H:i',
            'med_horarios.*.saida_almoco' => 'date_format:H:i',
            'med_horarios.*.retorno_almoco' => 'date_format:H:i',
            'med_horarios.*.saida' => 'date_format:H:i',
            'med_horarios.*.valor_hora' => 'numeric|min:0',
            'med_horarios.*.total' => 'numeric|min:0',
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
            'glosa_motivo' => 'nullable|string',
            'glosa_valor' => 'nullable|numeric|min:0',
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
        $data['glosa_valor'] = $request->boolean('glosar') ? $request->input('glosa_valor') : 0;
        $data['glosa_motivo'] = $request->boolean('glosar') ? $request->input('glosa_motivo') : null;

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
            'status' => 'aprovada_chefia',
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
            'status' => 'rejeitada',
            'motivo_rejeicao_chefia' => $request->motivo_rejeicao
        ]);

        return redirect()->back()->with('success', 'Nota rejeitada com sucesso!');
    }

    public function aceitar(Request $request, Nota $nota)
    {
        // Impede reprocessamento
        if ($nota->confirmado_financeiro_em) {
            return redirect()->back()->with('error', 'Esta nota já foi processada anteriormente.');
        }

        // Validação
        $validated = $request->validate([
            'comprovante' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'observacao' => 'nullable|string|max:1000',
        ]);

        // Upload do comprovante
        try {
            $comprovantePath = $request->file('comprovante')->store('comprovantes');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Falha ao fazer upload do comprovante.');
        }

        // Atualiza nota
        $nota->update([
            'status' => 'confirmada_financeiro',
            'confirmado_financeiro_em' => now(),
            'confirmado_financeiro_por' => auth()->id(),
            'comprovante_path' => $comprovantePath,
            'observacao_financeiro' => $validated['observacao'] ?? null,
        ]);

        return redirect()->back()->with('success', 'Nota finalizada com sucesso!');
    }

    public function recusar(Request $request, Nota $nota)
    {
        if ($nota->aprovada_financeiro_em) {
            return redirect()->back()->with('error', 'Nota já aprovada ou rejeitada.');
        }

        $nota->update([
            'status' => 'rejeitada',
        ]);

        return redirect()->back()->with('success', 'Nota rejeitada com sucesso!');
    }

    public function baixarComprovante(Nota $nota)
    {
        if (!$nota->comprovante_path || !Storage::exists($nota->comprovante_path)) {
            abort(404, 'Comprovante não encontrado.');
        }

        return Storage::download($nota->comprovante_path);
    }

}
