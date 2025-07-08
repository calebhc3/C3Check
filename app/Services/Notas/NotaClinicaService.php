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
    \Log::debug('Iniciando NotaClinicaService::handle', ['request' => $request->except(['arquivo_nf'])]);

    // Validação
    $validator = Validator::make($request->all(), [
        'tipo_nota' => 'required|in:clinica',
        'numero_nf' => 'nullable|string|max:50',
        'prestador' => 'required|string|max:255',
        'cnpj' => 'nullable|string|max:18',
        'valor_total' => 'required|numeric|min:0.01',
        'data_emissao' => 'required|date',
        'data_entregue_financeiro' => 'required|date',
        'status' => 'required|string',
        'clientes' => 'required|array|min:1',
        'clientes.*.cliente_atendido' => 'required|string|max:255',
        'clientes.*.valor' => 'required|numeric|min:0.01',
        'arquivo_nf' => 'required|array|min:1',
        'arquivo_nf.*' => 'file|mimes:pdf|max:10240',
        'taxa_correio' => 'sometimes|boolean',
        'glosar' => 'sometimes|boolean'
    ]);

    if ($validator->fails()) {
        \Log::warning('Validação falhou', ['errors' => $validator->errors()]);
        throw new ValidationException($validator);
    }

    return DB::transaction(function () use ($request) {
        // Prepara dados da nota
        $notaData = [
            'tipo_nota' => $request->tipo_nota,
            'numero_nf' => $request->numero_nf,
            'prestador' => $request->prestador,
            'cnpj' => $request->cnpj,
            'valor_total' => $request->valor_total,
            'data_emissao' => $request->data_emissao,
            'data_entregue_financeiro' => $request->data_entregue_financeiro,
            'status' => $request->status,
            'taxa_correio' => $request->taxa_correio ?? false,
            'valor_taxa_correio' => $request->taxa_correio ? ($request->valor_taxa_correio ?? 0) : 0,
            'glosa_valor' => $request->glosar ? ($request->glosa_valor ?? null) : null,
            'glosa_motivo' => $request->glosar ? ($request->glosa_motivo ?? null) : null,
            'user_id' => Auth::id()
        ];

        // Cria nota
        $nota = Nota::create($notaData);
        \Log::info('Nota criada', ['id' => $nota->id]);

        // Adiciona clientes
        foreach ($request->clientes as $cliente) {
            $nota->notaClientes()->create([
                'cliente_atendido' => $cliente['cliente_atendido'],
                'valor' => $cliente['valor'],
                'observacao' => $cliente['observacao'] ?? null
            ]);
        }

        // Armazena arquivos
        $caminhos = [];
        foreach ($request->file('arquivo_nf') as $arquivo) {
            $path = $arquivo->store('notas/' . now()->format('Y/m'), 'public');
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
        'data_emissao' => 'required|date',
        'data_entregue_financeiro' => 'required|date',
        'status' => 'required|string',
        'valor_total' => 'required|numeric|min:0.01',
        'clientes' => 'required|array|min:1',
        'clientes.*.cliente_atendido' => 'required|string|max:255',
        'clientes.*.valor' => 'required|numeric|min:0.01',
        'arquivo_nf' => 'required|array|min:1',
        'arquivo_nf.*' => 'file|mimes:pdf|max:10240',
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
        'data_entregue_financeiro' => $data['data_entregue_financeiro'],
        'status' => $data['status'],
        'valor_total' => $data['valor_total'],
        'taxa_correio' => $data['taxa_correio'] ?? false,
        'valor_taxa_correio' => ($data['taxa_correio'] ?? false) ? ($data['valor_taxa_correio'] ?? 0) : 0,
        'glosa_valor' => ($data['glosar'] ?? false) ? ($data['glosa_valor'] ?? null) : null,
        'glosa_motivo' => ($data['glosar'] ?? false) ? ($data['glosa_motivo'] ?? null) : null,
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