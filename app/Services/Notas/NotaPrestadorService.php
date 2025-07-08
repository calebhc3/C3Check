<?php

namespace App\Services\Notas;

use App\Models\Nota;
use App\Models\NotaCliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class NotaPrestadorService
{
    public function handle(Request $request)
    {
        $validated = $this->validate($request);
        $this->verificaConsistenciaFinanceira($validated);

        return DB::transaction(function () use ($validated, $request) {
            $nota = Nota::create($this->mapData($validated));
            $this->createClientes($nota, $validated['prest_clientes']);
            $this->storeArquivos($request, $nota);
            
            Log::info('Nota de prestador cadastrada', ['nota_id' => $nota->id]);
            return $nota;
        });
    }

    protected function validate(Request $request): array
    {
        $rules = [
            'tipo_nota' => 'required|in:prestador',
            'prest_numero_nf' => 'required|string|max:50',
            'prest_prestador' => 'required|string|max:255',
            'prest_cnpj' => 'required|string|max:18',
            'prest_valor_total' => 'required|numeric|min:0.01',
            'prest_vencimento_original' => 'required|date',
            'prest_vencimento_prorrogado' => 'nullable|date|after_or_equal:prest_vencimento_original',
            'prest_mes' => 'nullable|string|max:7|regex:/^\d{2}\/\d{4}$/',
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
            'status' => 'required|in:lancada,pendente,cancelada',
            'arquivo_nf' => 'required|array|min:1',
            'arquivo_nf.*' => 'file|mimes:pdf|max:10240',
        ];

        $messages = [
            'required' => 'O campo :attribute é obrigatório.',
            'required_if' => 'O campo :attribute é obrigatório quando :other está marcado.',
            'prest_mes.regex' => 'O mês deve estar no formato MM/AAAA',
            'prest_estado.max' => 'O estado deve ter 2 caracteres'
        ];

        return Validator::make($request->all(), $rules, $messages)->validate();
    }

    protected function verificaConsistenciaFinanceira(array $data): void
    {
        $totalClientes = collect($data['prest_clientes'])->sum('valor');
        $totalCalculado = $totalClientes + ($data['prest_taxa_correio'] ? $data['prest_valor_taxa_correio'] : 0);

        if (abs($totalCalculado - $data['prest_valor_total']) > 0.01) {
            throw ValidationException::withMessages([
                'prest_valor_total' => 'O valor total não corresponde à soma dos clientes e taxas'
            ]);
        }
    }

    protected function mapData(array $data): array
    {
        return [
            'tipo_nota' => 'prestador',
            'numero_nf' => $data['prest_numero_nf'],
            'prestador' => $data['prest_prestador'],
            'cnpj' => $data['prest_cnpj'],
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
            'status' => $data['status'],
            'arquivo_nf' => null, // Será preenchido depois
        ];
    }

    protected function createClientes(Nota $nota, array $clientes): void
    {
        foreach ($clientes as $cliente) {
            NotaCliente::create([
                'nota_id' => $nota->id,
                'cliente_atendido' => $cliente['cliente_atendido'],
                'valor' => $cliente['valor'],
                'observacao' => $cliente['observacao'] ?? null,
            ]);
        }
    }

    protected function storeArquivos(Request $request, Nota $nota): void
    {
        $caminhos = [];
        foreach ($request->file('arquivo_nf') as $arquivo) {
            $nome = 'NF_'.$nota->id.'_'.time().'_'.$arquivo->getClientOriginalName();
            $path = $arquivo->storeAs('notas/prestador', $nome, 'public');
            $caminhos[] = $path;
        }
        
        $nota->update(['arquivo_nf' => json_encode($caminhos)]);
    }

}