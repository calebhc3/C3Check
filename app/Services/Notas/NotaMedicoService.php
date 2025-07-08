<?php

namespace App\Services\Notas;

use App\Models\Nota;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class NotaMedicoService
{
    public function handle(Request $request)
    {
        $validated = $this->validate($request);
        $this->verificaConsistenciaFinanceira($validated);

        return DB::transaction(function () use ($validated, $request) {
            $nota = Nota::create($this->mapData($validated));
            $this->storeArquivos($request, $nota);
            
            Log::info('Nota de médico cadastrada', ['nota_id' => $nota->id]);
            return $nota;
        });
    }

    protected function validate(Request $request): array
    {
        $rules = [
            'tipo_nota' => 'required|in:medico',
            'med_nome' => 'required|string|max:255',
            'med_crm' => 'nullable|string|max:50',
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
            'med_deslocamento' => 'sometimes|boolean',
            'med_valor_deslocamento' => 'required_if:med_deslocamento,true|numeric|min:0',
            'med_cobrou_almoco' => 'sometimes|boolean',
            'med_valor_almoco' => 'required_if:med_cobrou_almoco,true|numeric|min:0',
            'med_reembolso_correios' => 'sometimes|boolean',
            'med_valor_correios' => 'required_if:med_reembolso_correios,true|numeric|min:0',
            'med_valor_total_final' => 'required|numeric|min:0',
            'med_dados_bancarios' => 'nullable|json',
            'med_numero_nf' => 'required|string|max:255',
            'med_vencimento_original' => 'required|date',
            'med_mes' => 'nullable|string|max:7|regex:/^\d{2}\/\d{4}$/',
            'med_vencimento_prorrogado' => 'nullable|date|after_or_equal:med_vencimento_original',
            'med_tipo_pagamento' => 'nullable|in:boleto,deposito,pix',
            'med_observacao' => 'nullable|string|max:1000',
            'status' => 'required|in:lancada,pendente,cancelada',
            'arquivo_nf' => 'required|array|min:1',
            'arquivo_nf.*' => 'file|mimes:pdf|max:10240',
        ];

        $messages = [
            'required' => 'O campo :attribute é obrigatório.',
            'required_if' => 'O campo :attribute é obrigatório quando :other está marcado.',
            'med_horarios.*.data.required' => 'A data do horário é obrigatória.',
            'med_horarios.*.entrada.required' => 'O horário de entrada é obrigatório.',
            'med_mes.regex' => 'O mês deve estar no formato MM/AAAA'
        ];

        return Validator::make($request->all(), $rules, $messages)->validate();
    }

    protected function verificaConsistenciaFinanceira(array $data): void
    {
        $totalHorarios = collect($data['med_horarios'])->sum('total');

        $deslocamento = data_get($data, 'med_deslocamento', false);
        $valorDeslocamento = $deslocamento ? $data['med_valor_deslocamento'] : 0;

        $almoco = data_get($data, 'med_cobrou_almoco', false);
        $valorAlmoco = $almoco ? $data['med_valor_almoco'] : 0;

        $correios = data_get($data, 'med_reembolso_correios', false);
        $valorCorreios = $correios ? $data['med_valor_correios'] : 0;

        $totalAdicionais = $valorDeslocamento + $valorAlmoco + $valorCorreios;

        $totalCalculado = $totalHorarios + $totalAdicionais;

        if (abs($totalCalculado - $data['med_valor_total_final']) > 0.01) {
            throw ValidationException::withMessages([
                'med_valor_total_final' => 'O valor total não bate com a soma dos horários e adicionais'
            ]);
        }
    }

    protected function mapData(array $data): array
    {
        return [
            'tipo_nota' => 'medico',
            'numero_nf' => $data['med_numero_nf'],
            'vencimento_original' => $data['med_vencimento_original'],
            'vencimento_prorrogado' => $data['med_vencimento_prorrogado'] ?? null,
            'mes' => $data['med_mes'] ?? null,
            'tipo_pagamento' => $data['med_tipo_pagamento'] ?? null,
            'dados_bancarios' => $data['med_dados_bancarios'] ?? null,
            'observacao' => $data['med_observacao'] ?? null,
            
            // Campos específicos do médico
            'med_nome' => $data['med_nome'],
            'med_crm' => $data['med_crm'] ?? null,
            'med_telefone' => $data['med_telefone'] ?? null,
            'med_email' => $data['med_email'] ?? null,
            'med_cliente_atendido' => $data['med_cliente_atendido'],
            'med_local' => $data['med_local'] ?? null,
            'med_horarios' => json_encode($data['med_horarios']),
            'med_valor_total_final' => $data['med_valor_total_final'],
            'med_deslocamento' => $data['med_deslocamento'] ?? false,
            'med_valor_deslocamento' => $data['med_deslocamento'] ? $data['med_valor_deslocamento'] : 0,
            'med_cobrou_almoco' => $data['med_cobrou_almoco'] ?? false,
            'med_valor_almoco' => $data['med_cobrou_almoco'] ? $data['med_valor_almoco'] : 0,
            'med_reembolso_correios' => $data['med_reembolso_correios'] ?? false,
            'med_valor_correios' => $data['med_reembolso_correios'] ? $data['med_valor_correios'] : 0,
            
            // Campos comuns
            'user_id' => Auth::id(),
            'status' => $data['status'],
            'arquivo_nf' => null, // Será preenchido depois
        ];
    }

    protected function storeArquivos(Request $request, Nota $nota): void
    {
        $caminhos = [];
        foreach ($request->file('arquivo_nf') as $arquivo) {
            $nome = 'NF_'.$nota->id.'_'.time().'_'.$arquivo->getClientOriginalName();
            $path = $arquivo->storeAs('notas/medico', $nome, 'public');
            $caminhos[] = $path;
        }
        
        $nota->update(['arquivo_nf' => json_encode($caminhos)]);
    }
}