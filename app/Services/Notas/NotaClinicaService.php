<?php

namespace App\Services\Notas;

use App\Models\Nota;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class NotaClinicaService
{
public function handle(Request $request)
{
    \Log::debug('Dados completos do request', [
    'all' => $request->all(),
    'files' => $request->file(),
    'has_arquivo_nf' => $request->hasFile('arquivo_nf')
]);
    // Filtra clientes vazios
    $request->merge([
        'clientes' => array_filter($request->clientes ?? [], function($cliente) {
            return !empty($cliente['cliente_atendido']) && $cliente['valor'] > 0;
        })
    ]);

    \Log::debug('Arquivos recebidos', [
        'hasFile' => $request->hasFile('arquivo_nf'),
        'files' => $request->file('arquivo_nf') ? array_map(function($file) {
            return [
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime' => $file->getMimeType()
            ];
        }, $request->file('arquivo_nf')) : 'Nenhum arquivo'
    ]);

    // Validação
    $validator = Validator::make($request->all(), [
        'tipo_nota' => 'required|in:clinica',
        'numero_nf' => 'nullable|string|max:50',
        'prestador' => 'required|string|max:255',
        'cidade' => 'nullable|string|max:100',
        'estado' => 'nullable|string|max:2',
        'regiao' => 'nullable|string|max:50',
        'mes' => 'nullable|string|max:7|regex:/^\d{2}\/\d{4}$/',
        'cnpj' => 'nullable|string|max:18',
        'valor_total' => 'required|numeric|min:0.01',
        'vencimento_original' => 'required|date',
        'clientes' => 'required|array|min:1',
        'clientes.*.cliente_atendido' => 'required_with:clientes|string|max:255',
        'clientes.*.valor' => 'required_with:clientes|numeric|min:0.01',
        'arquivo_nf' => 'required|array|min:1',
        'arquivo_nf.*' => 'required|file|mimes:pdf|max:10240',
    ]);

    if ($validator->fails()) {
        \Log::error('Validação falhou', ['errors' => $validator->errors()->all()]);
        throw new ValidationException($validator);
    }

    return DB::transaction(function () use ($request) {
        $notaData = [
            'tipo_nota' => $request->tipo_nota,
            'numero_nf' => $request->numero_nf ?? null,
            'prestador' => $request->prestador,
            'cidade' => $request->cidade ?? null,
            'estado' => $request->estado ?? null,
            'regiao' => $request->regiao ?? null,
            'mes' => $request->mes ?? null,
            'vencimento_original' => $request->vencimento_original ?? null,
            'vencimento_prorrogado' => $request->vencimento_prorrogado ?? null,
            'taxa_correio' => $request->taxa_correio ?? false,
            'valor_taxa_correio' => $request->valor_taxa_correio ?? 0,
            'glosar' => $request->glosar ?? false,
            'glosa_valor' => $request->glosar ? ($request->glosa_valor ?? null) : null,
            'glosa_motivo' => $request->glosar ? ($request->glosa_motivo ?? null) : null,
            'tipo_pagamento' => $request->tipo_pagamento ?? null,
            'cnpj' => $request->cnpj ?? null,
            'observacao' => $request->observacao ?? null,
            'valor_total' => $request->valor_total,
            'data_emissao' => $request->data_emissao ?? now(),
            'dados_bancarios' => $request->dados_bancarios ?? null,
            'status' => 'lancada', // Valor padrão definido
            'user_id' => Auth::id()
        ];

        $nota = Nota::create($notaData);

        foreach ($request->clientes as $cliente) {
            $nota->notaClientes()->create([
                'cliente_atendido' => $cliente['cliente_atendido'],
                'valor' => $cliente['valor'],
                'observacao' => $cliente['observacao'] ?? null
            ]);
        }

        $caminhos = [];
        foreach ($request->file('arquivo_nf') as $arquivo) {
            $nome = 'NF_' . ($request->numero_nf ?? 'sem_numero') . '_' . uniqid() . '.pdf';
            $path = $arquivo->storeAs('notas/' . now()->format('Y/m'), $nome, 'public');
            $caminhos[] = $path;
        }

        $nota->update(['arquivo_nf' => $caminhos]);
        
        return $nota;
    });
}
private function createValidator(Request $request)
{
    $rules = [
        'tipo_nota' => 'required|in:clinica',
        'prestador' => 'required|string|max:255',
        'valor_total' => 'required|numeric|min:0.01',
        'clientes' => 'required|array|min:1',
        'clientes.*.cliente_atendido' => 'required|string|max:255',
        'clientes.*.valor' => 'required|numeric|min:0.01',
        'arquivo_nf' => 'required|array|min:1',
        'arquivo_nf.*' => 'required|file|mimes:pdf|max:10240',
        'taxa_correio' => 'sometimes|boolean',
        'valor_taxa_correio' => 'nullable|numeric|min:0|required_if:taxa_correio,true',
        'glosar' => 'sometimes|boolean',
        'glosa_valor' => 'nullable|numeric|min:0|required_if:glosar,true',
        'glosa_motivo' => 'nullable|string|max:500|required_if:glosar,true',
    ];

    return Validator::make($request->all(), $rules);
}

    private function normalizeClientes(array $clientes): array
    {
        return array_map(function ($c) {
            return [
                'cliente_atendido' => $c['cliente_atendido'] ?? '',
                'valor' => (float) ($c['valor'] ?? 0),
                'observacao' => $c['observacao'] ?? null,
            ];
        }, $clientes);
    }

    private function hasValidClientes(array $clientes): bool
    {
        foreach ($clientes as $c) {
            if (!empty(trim($c['cliente_atendido'])) && $c['valor'] > 0) {
                return true;
            }
        }
        return false;
    }
private function prepareNotaData(array $data): array
{
    return [
        'tipo_nota' => $data['tipo_nota'],
        'prestador' => $data['prestador'],
        'data_emissao' => $data['data_emissao'],
        'cidade' => $data['cidade'] ?? null,
        'estado' => $data['estado'] ?? null,
        'regiao' => $data['regiao'] ?? null,
        'status' => $data['status'],
        'valor_total' => $data['valor_total'],
        'taxa_correio' => $data['taxa_correio'] ?? false,
        'vencimento_original' => $data['vencimento_original'] ?? null,
        'tipo_pagamento' => $data['tipo_pagamento'] ?? null,
        'mes' => $data['mes'] ?? null,
        'clientes' => $this->normalizeClientes($data['clientes'] ?? []),
        'arquivo_nf' => $data['arquivo_nf'] ?? [],
        'glosar' => $data['glosar'] ?? false,
        'dados_bancarios' => $data['dados_bancarios'] ?? null,
        'vencimento_prorrogado' => $data['vencimento_prorrogado'] ?? null,
        'valor_taxa_correio' => ($data['taxa_correio'] ?? false) ? ($data['valor_taxa_correio'] ?? 0) : 0,
        'glosa_valor' => ($data['glosar'] ?? false) ? ($data['glosa_valor'] ?? null) : null,
        'glosa_motivo' => ($data['glosar'] ?? false) ? ($data['glosa_motivo'] ?? null) : null,
        'observacao' => $data['observacao'] ?? null,
        'user_id' => Auth::id(),
        // Outros campos
        'numero_nf' => $data['numero_nf'] ?? null,
        'cnpj' => $data['cnpj'] ?? null,
    ];
}

    private function createClientes(Nota $nota, array $clientes): void
    {
        foreach ($clientes as $cliente) {
            $nota->notaClientes()->create([
                'cliente_atendido' => $cliente['cliente_atendido'],
                'valor' => (float) $cliente['valor'],
                'observacao' => $cliente['observacao'] ?? null,
            ]);
        }
    }

    private function storeArquivos(Request $request, Nota $nota): void
    {
        if (!$request->hasFile('arquivo_nf')) {
            Log::warning('Nenhum arquivo enviado para nota', ['nota_id' => $nota->id]);
            return;
        }

        $caminhos = [];
        foreach ($request->file('arquivo_nf') as $arquivo) {
            $nome = 'NF_' . ($nota->numero_nf ?? 'sem_numero') . '_' . uniqid() . '.pdf';
            $path = $arquivo->storeAs('notas/' . now()->format('Y/m'), $nome, 'public');
            $caminhos[] = $path;
            Log::debug('Arquivo armazenado', ['path' => $path]);
        }

        $nota->update(['arquivo_nf' => $caminhos]);
    }
}