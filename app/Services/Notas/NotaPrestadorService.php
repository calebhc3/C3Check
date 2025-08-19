<?php

namespace App\Services\Notas;

use App\Models\Nota;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class NotaPrestadorService
{
    public function handle(Request $request)
    {
        Log::debug('Dados completos do request', [
            'all' => $request->all(),
            'files' => $request->file(),
            'has_arquivo_nf' => $request->hasFile('prest_arquivo_nf'),
        ]);

        // Normaliza booleanos e tipos antes da validação
        $this->normalizeInput($request);

        // Filtra clientes vazios
        $request->merge([
            'prest_clientes' => array_filter($request->prest_clientes ?? [], function ($cliente) {
                return !empty(trim($cliente['cliente_atendido'] ?? '')) && (float)($cliente['valor'] ?? 0) > 0;
            }),
        ]);

        Log::debug('Arquivos recebidos', [
            'hasFile' => $request->hasFile('prest_arquivo_nf'),
            'files' => $request->file('prest_arquivo_nf') ? array_map(function ($file) {
                return [
                    'name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime' => $file->getMimeType(),
                ];
            }, $request->file('prest_arquivo_nf')) : 'Nenhum arquivo',
        ]);

        $validator = $this->createValidator($request);
        if ($validator->fails()) {
            Log::error('Validação falhou', ['errors' => $validator->errors()->all()]);
            throw new ValidationException($validator);
        }

        $validated = $validator->validated();

        $this->verificaConsistenciaFinanceira($validated);

        return DB::transaction(function () use ($validated, $request) {
            $notaData = $this->prepareNotaData($validated);

            $nota = Nota::create($notaData);

            $this->createClientes($nota, $validated['prest_clientes']);

            $this->storeArquivos($request, $nota);

            Log::info('Nota de prestador cadastrada', ['nota_id' => $nota->id]);

            return $nota;
        });
    }

    protected function normalizeInput(Request $request): void
    {
        $data = $request->all();

        $boolFields = ['prest_taxa_correio', 'prest_glosar'];
        foreach ($boolFields as $field) {
            $data[$field] = isset($data[$field]) ? filter_var($data[$field], FILTER_VALIDATE_BOOLEAN) : false;
        }

        if (!empty($data['prest_clientes']) && is_array($data['prest_clientes'])) {
            foreach ($data['prest_clientes'] as &$cliente) {
                $cliente['cliente_atendido'] = $cliente['cliente_atendido'] ?? '';
                $cliente['valor'] = isset($cliente['valor']) ? (float)$cliente['valor'] : 0;
                $cliente['observacao'] = $cliente['observacao'] ?? null;
            }
            unset($cliente);
        }

        $stringFields = [
            'prest_numero_nf', 'prest_prestador', 'prest_cnpj', 'prest_cidade',
            'prest_estado', 'prest_regiao', 'prest_tipo_pagamento', 'prest_dados_bancarios',
            'prest_observacao', 'prest_glosa_motivo'
        ];

        foreach ($stringFields as $field) {
            if (!isset($data[$field]) || is_null($data[$field])) {
                $data[$field] = '';
            }
        }

        $data['prest_valor_total'] = isset($data['prest_valor_total']) ? (float)$data['prest_valor_total'] : 0;
        $data['prest_valor_taxa_correio'] = isset($data['prest_valor_taxa_correio']) ? (float)$data['prest_valor_taxa_correio'] : 0;
        $data['prest_glosa_valor'] = isset($data['prest_glosa_valor']) ? (float)$data['prest_glosa_valor'] : 0;

        $request->merge($data);
    }

    protected function createValidator(Request $request)
    {
        return Validator::make($request->all(), [
            'tipo_nota' => 'required|in:prestador',
            'prest_numero_nf' => 'string|max:50',
            'prest_cidade' => 'nullable|string|max:100',
            'prest_estado' => 'nullable|string|max:2',
            'prest_regiao' => 'nullable|string|max:50',
            'prest_prestador' => 'required|string|max:255',
            'prest_cnpj' => 'string|max:18',
            'prest_valor_total' => 'required|numeric|min:0.01',
            'prest_vencimento_original' => 'required|date',
            'prest_vencimento_prorrogado' => 'nullable|date|after_or_equal:prest_vencimento_original',
            'prest_mes' => ['nullable', 'string', 'max:7', 'regex:/^\d{2}\/\d{4}$/'],
            'prest_tipo_pagamento' => 'nullable|in:boleto,deposito,pix',
            'prest_dados_bancarios' => 'nullable|string|max:500',
            'prest_cidade' => 'nullable|string|max:255',
            'prest_estado' => 'nullable|string|max:2',
            'prest_regiao' => 'nullable|in:Norte,Nordeste,Centro-Oeste,Sudeste,Sul',
            'prest_observacao' => 'nullable|string|max:1000',
            'prest_taxa_correio' => 'sometimes|boolean',
            'prest_valor_taxa_correio' => 'required_if:prest_taxa_correio,true|numeric|min:0',
            'prest_clientes' => 'required|array|min:1',
            'prest_clientes.*.cliente_atendido' => 'required|string|max:255',
            'prest_clientes.*.valor' => 'required|numeric|min:0.01',
            'prest_clientes.*.observacao' => 'nullable|string|max:500',
            'prest_glosar' => 'sometimes|boolean',
            'prest_glosa_valor' => 'required_if:prest_glosar,true|numeric|min:0',
            'prest_glosa_motivo' => 'required_if:prest_glosar,true|string|max:500',
            'prest_arquivo_nf' => 'required|array|min:1',
            'prest_arquivo_nf.*' => 'required|file|mimes:pdf|max:10240',
        ], [
            'required' => 'O campo :attribute é obrigatório.',
            'required_if' => 'O campo :attribute é obrigatório quando :other está marcado.',
            'prest_mes.regex' => 'O mês deve estar no formato MM/AAAA',
            'prest_estado.max' => 'O estado deve ter 2 caracteres',
        ]);
    }

    protected function verificaConsistenciaFinanceira(array $data): void
    {
        $totalClientes = collect($data['prest_clientes'])->sum(fn($c) => (float) $c['valor']);
        $taxaCorreio = $data['prest_taxa_correio'] ? (float) ($data['prest_valor_taxa_correio'] ?? 0) : 0;
        $totalCalculado = $totalClientes + $taxaCorreio;

        if (abs($totalCalculado - (float) $data['prest_valor_total']) > 0.01) {
            throw ValidationException::withMessages([
                'prest_valor_total' => 'O valor total não corresponde à soma dos clientes e taxas',
            ]);
        }
    }

    protected function prepareNotaData(array $data): array
    {
        return [
            'tipo_nota' => $data['tipo_nota'],
            'numero_nf' => $data['prest_numero_nf'] ?? null,
            'prestador' => $data['prest_prestador'],
            'cidade' => $data['prest_cidade'] ?? null,
            'estado' => $data['prest_estado'] ?? null,
            'regiao' => $data['prest_regiao'] ?? null,
            'cnpj' => $data['prest_cnpj'] ?? null,
            'valor_total' => $data['prest_valor_total'],
            'vencimento_original' => $data['prest_vencimento_original'],
            'vencimento_prorrogado' => $data['prest_vencimento_prorrogado'] ?? null,
            'mes' => $data['prest_mes'] ?? null,
            'tipo_pagamento' => $data['prest_tipo_pagamento'] ?? null,
            'dados_bancarios' => $data['prest_dados_bancarios'] ?? null,
            'cidade' => $data['prest_cidade'] ?? null,
            'estado' => $data['prest_estado'] ?? null,
            'regiao' => $data['prest_regiao'] ?? null,
            'observacao' => $data['prest_observacao'] ?? null,
            'taxa_correio' => $data['prest_taxa_correio'] ?? false,
            'valor_taxa_correio' => $data['prest_taxa_correio'] ? $data['prest_valor_taxa_correio'] : 0,
            'glosar' => $data['prest_glosar'] ?? false,
            'glosa_valor' => $data['prest_glosar'] ? $data['prest_glosa_valor'] : 0,
            'glosa_motivo' => $data['prest_glosar'] ? $data['prest_glosa_motivo'] : null,
            'user_id' => Auth::id(),
            'status' => 'lancada',
            'arquivo_nf' => null,
            'observacao' => $data['prest_observacao'] ?? null,
        ];
    }

    protected function createClientes(Nota $nota, array $clientes): void
    {
        foreach ($clientes as $cliente) {
            $nota->notaClientes()->create([
                'cliente_atendido' => $cliente['cliente_atendido'],
                'valor' => (float) $cliente['valor'],
                'observacao' => $cliente['observacao'] ?? null,
            ]);
        }
    }

    protected function storeArquivos(Request $request, Nota $nota): void
    {
        if (!$request->hasFile('prest_arquivo_nf')) {
            Log::warning('Nenhum arquivo enviado para nota', ['nota_id' => $nota->id]);
            return;
        }

        $caminhos = [];
        foreach ($request->file('prest_arquivo_nf') as $arquivo) {
            $nome = 'NF_' . ($nota->numero_nf ?? 'sem_numero') . '_' . uniqid() . '.pdf';
            $path = $arquivo->storeAs('notas/' . now()->format('Y/m'), $nome, 'public');
            $caminhos[] = $path;
            Log::debug('Arquivo armazenado', ['path' => $path]);
        }

        $nota->update(['arquivo_nf' => $caminhos]);
    }
}
