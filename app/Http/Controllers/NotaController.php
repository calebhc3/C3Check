<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Nota;
use Illuminate\Support\Facades\Storage;
use App\Models\NotaCliente;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use App\Services\Notas\NotaClinicaService;
use App\Services\Notas\NotaMedicoService;
use App\Services\Notas\NotaPrestadorService;
use Illuminate\Foundation\Validation\ValidatesRequests;

class NotaController extends Controller
{
    use ValidatesRequests;
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
        $request->validate([
            'tipo_nota' => 'required|in:clinica,medico,prestador'
        ]);

        $tipoNota = $request->input('tipo_nota');

        $service = match ($tipoNota) {
            'clinica' => new NotaClinicaService(),
            'medico' => new NotaMedicoService(),
            'prestador' => new NotaPrestadorService(),
        };

        $nota = $service->handle($request);

        return redirect()
            ->route('notas.detalhes', $nota)
            ->with('success', 'Nota cadastrada com sucesso!');
    } catch (ValidationException $e) {
        return back()
            ->withErrors($e->validator)
            ->withInput();
    } catch (\Exception $e) {
        Log::error('Erro ao salvar nota: ' . $e->getMessage(), [
            'exception' => $e,
            'request' => $request->all()
        ]);

        return back()
            ->with('error', 'Erro ao salvar nota: ' . $e->getMessage())
            ->withInput();
    }
}


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Nota $nota)
    {
        $this->authorizeNota($nota);
        
        // Carrega os clientes associados (para notas clínicas e de prestador)
        $nota->load('notaClientes');
        
        // Se for nota de médico, decodifica os horários
        if ($nota->tipo_nota === 'medico' && $nota->med_horarios) {
            $nota->med_horarios = json_decode($nota->med_horarios, true);
        }
        
        return view('notas.edit', compact('nota'));
    }

    public function detalhes($id)
    {
        $nota = Nota::with(['notaclientes'])->findOrFail($id);

        if ($nota->tipo_nota === 'clinica') {
            return view('notas.partials.detalhes-clinica', compact('nota'));
        }

        if ($nota->tipo_nota === 'medico') {
            $nota->med_horarios = json_decode($nota->med_horarios, true);
            return view('notas.partials.detalhes-medico', compact('nota'));
        }

        if ($nota->tipo_nota === 'prestador') {
            return view('notas.partials.detalhes-prestador', compact('nota'));
        }

        return response()->json(['erro' => 'Tipo de nota inválido.'], 422);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Nota $nota)
    {
        $tipoNota = $request->input('tipo_nota', $nota->tipo_nota);

        switch ($tipoNota) {
            case 'clinica':
                return $this->updateClinica($request, $nota);
            case 'medico':
                return $this->updateMedico($request, $nota);
            case 'prestador':
                return $this->updatePrestador($request, $nota);
            default:
                return back()->with('error', 'Tipo de nota inválido');
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

        // Flags booleanas
        $deslocamento = $request->has('med_deslocamento');
        $cobrouAlmoco = $request->has('med_cobrou_almoco');
        $reembolsoCorreios = $request->has('med_reembolso_correios');

        // Sanitiza campos com base nas flags
        $valorDeslocamento = $deslocamento ? ($validated['med_valor_deslocamento'] ?? 0) : 0;
        $valorAlmoco = $cobrouAlmoco ? ($validated['med_valor_almoco'] ?? 0) : 0;
        $valorCorreios = $reembolsoCorreios ? ($validated['med_valor_correios'] ?? 0) : 0;

        // Soma total: horas + deslocamento + almoço
        $valorHoras = collect($validated['med_horarios'])->sum('total');
        $valorTotalFinal = $valorHoras + $valorDeslocamento + $valorAlmoco;

        // Encode dos horários
        $horariosJson = json_encode($validated['med_horarios']);

        // Encode dos dados bancários (se houver)
        $dadosBancarios = !empty($validated['med_dados_bancarios'])
            ? json_encode(['dados' => $validated['med_dados_bancarios']])
            : null;

        // Campos adicionais mapeados
        $mapaCampos = [
            'med_numero_nf' => 'numero_nf',
            'med_vencimento_original' => 'vencimento_original',
            'med_mes' => 'mes',
            'med_vencimento_prorrogado' => 'vencimento_prorrogado',
            'med_tipo_pagamento' => 'tipo_pagamento',
            'med_dados_bancarios' => 'dados_bancarios',
            'med_observacao' => 'observacao',
        ];

        // Montagem do array final
        $data = [
            'tipo_nota' => $validated['tipo_nota'],
            'med_nome' => $validated['med_nome'],
            'med_telefone' => $validated['med_telefone'] ?? null,
            'med_email' => $validated['med_email'] ?? null,
            'med_cliente_atendido' => $validated['med_cliente_atendido'],
            'med_local' => $validated['med_local'] ?? null,
            'med_horarios' => $horariosJson,
            'med_valor_total_final' => $valorTotalFinal,
            'med_deslocamento' => $deslocamento,
            'med_valor_deslocamento' => $valorDeslocamento,
            'med_cobrou_almoco' => $cobrouAlmoco,
            'med_valor_almoco' => $valorAlmoco,
            'med_almoco_inicio' => $cobrouAlmoco ? ($validated['med_almoco_inicio'] ?? null) : null,
            'med_almoco_fim' => $cobrouAlmoco ? ($validated['med_almoco_fim'] ?? null) : null,
            'med_reembolso_correios' => $reembolsoCorreios,
            'med_valor_correios' => $valorCorreios,
            'dados_bancarios' => $dadosBancarios,
            'glosa_valor' => $request->boolean('glosar') ? $request->input('glosa_valor', 0) : 0,
            'glosa_motivo' => $request->boolean('glosar') ? $request->input('glosa_motivo') : null,
            'user_id' => auth()->id(),
            'status' => 'lancada',
        ];

        // Adiciona campos mapeados extras, se existirem
        foreach ($mapaCampos as $campoRequest => $campoDB) {
            if (!empty($validated[$campoRequest])) {
                $data[$campoDB] = $validated[$campoRequest];
            }
        }

        $nota->update($data);

        return redirect()->route('dashboard')->with('success', 'Nota de médico atualizada com sucesso!');
    }

    protected function updatePrestador(Request $request, Nota $nota)
    {
        // Configurações para upload de arquivo grande
        ini_set('memory_limit', '256M');
        ini_set('upload_max_filesize', '100M');
        ini_set('post_max_size', '100M');

        // Validação manual do arquivo
        if ($request->hasFile('arquivo_nf')) {
            $file = $request->file('arquivo_nf')[0];
            if ($file->getSize() > 100 * 1024 * 1024) {
                return back()->withErrors(['arquivo_nf' => 'Arquivo excede 100MB']);
            }
            if ($file->getClientOriginalExtension() !== 'pdf') {
                return back()->withErrors(['arquivo_nf' => 'Apenas PDFs são aceitos']);
            }
        }

        // Validação dos dados
        $validatedData = $request->validate([
            'tipo_nota' => 'required|in:clinica,medico,prestador',
            'prest_numero_nf' => 'required|string|max:50',
            'prest_prestador' => 'required|string|max:255',
            'prest_cnpj' => 'required|string|max:18',
            'prest_valor_total' => 'required|numeric|min:0.01',
            'prest_vencimento_original' => 'required|date',
            'prest_vencimento_prorrogado' => 'nullable|date|after_or_equal:prest_vencimento_original',
            'prest_mes' => 'nullable|string|max:7|regex:/^\d{2}\/\d{4}$/',
            'prest_tipo_pagamento' => 'nullable|in:boleto,deposito,pix',
            'prest_dados_bancarios' => 'nullable|string|max:500',
            'prest_taxa_correio' => 'sometimes|boolean',
            'prest_valor_taxa_correio' => 'nullable|numeric|min:0|required_if:prest_taxa_correio,true',
            'arquivo_nf' => 'sometimes|array',
            'arquivo_nf.*' => 'sometimes|file|mimes:pdf|max:102400',
            'prest_clientes' => 'required|array|min:1',
            'prest_clientes.*.cliente_atendido' => 'required|string|max:255',
            'prest_clientes.*.valor' => 'required|numeric|min:0.01',
            'prest_clientes.*.observacao' => 'nullable|string|max:500',
            'prest_glosar' => 'sometimes|boolean',
            'prest_glosa_valor' => 'nullable|numeric|min:0|required_if:prest_glosar,true',
            'prest_glosa_motivo' => 'nullable|string|max:500|required_if:prest_glosar,true',
        ]);

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
                    $fileName = 'NF_' . ($validatedData['prest_numero_nf'] ?? 'sem_numero') . '_' . time() . '_' . uniqid() . '.pdf';
                    $path = $arquivo->storeAs("notas/$ano/$mes", $fileName, 'public');
                    $caminhosArquivos[] = $path;
                }
            }

            // Atualiza dados da nota
            $nota->update([
                'tipo_nota' => 'prestador',
                'numero_nf' => $validatedData['prest_numero_nf'],
                'prestador' => $validatedData['prest_prestador'],
                'cnpj' => $validatedData['prest_cnpj'],
                'valor_total' => $validatedData['prest_valor_total'],
                'vencimento_original' => $validatedData['prest_vencimento_original'],
                'vencimento_prorrogado' => $validatedData['prest_vencimento_prorrogado'] ?? null,
                'mes' => $validatedData['prest_mes'] ?? null,
                'tipo_pagamento' => $validatedData['prest_tipo_pagamento'] ?? null,
                'dados_bancarios' => $validatedData['prest_dados_bancarios'] ?? null,
                'taxa_correio' => $validatedData['prest_taxa_correio'] ?? false,
                'valor_taxa_correio' => $validatedData['prest_taxa_correio'] ? ($validatedData['prest_valor_taxa_correio'] ?? 0) : 0,
                'arquivo_nf' => !empty($caminhosArquivos) ? json_encode($caminhosArquivos) : $nota->arquivo_nf,
                'glosar' => $validatedData['prest_glosar'] ?? false,
                'glosa_valor' => ($validatedData['prest_glosar'] ?? false) ? ($validatedData['prest_glosa_valor'] ?? 0) : 0,
                'glosa_motivo' => ($validatedData['prest_glosar'] ?? false) ? ($validatedData['prest_glosa_motivo'] ?? null) : null,
            ]);

            // Atualiza clientes
            $nota->notaClientes()->delete();
            foreach ($validatedData['prest_clientes'] as $cliente) {
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
                ->with('success', 'Nota de prestador atualizada com sucesso!');

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
