<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Nota extends Model
{
    use HasFactory;

    protected $fillable = [
        // Campos comuns
        'tipo_nota',
        'numero_nf',
        'prestador',
        'cnpj',
        'valor_total',
        'taxa_correio',
        'valor_taxa_correio',
        'data_emissao',
        'vencimento_original',
        'data_entregue_financeiro',
        'mes',
        'vencimento_prorrogado',
        'tipo_pagamento',
        'dados_bancarios',
        'status',
        'arquivo_nf',
        'user_id',
        'aprovado_chefia_em',
        'aprovado_chefia_por',
        'confirmado_financeiro_em',
        'confirmado_financeiro_por',
        'glosa_motivo',
        'glosa_valor',
        'motivo_rejeicao_chefia',

        // Campos para médicos
        'med_nome',
        'med_telefone',
        'med_email',
        'med_cliente_atendido',
        'med_local',
        'med_horarios',
        'med_deslocamento',
        'med_valor_deslocamento',
        'med_cobrou_almoco',
        'med_valor_almoco',
        'med_almoco_inicio',
        'med_almoco_fim',
        'med_reembolso_correios',
        'med_valor_correios',
        'med_valor_total_final',
        'med_dados_bancarios',
        'motivo_rejeicao_chefia',
    ];

    protected $casts = [
        'dados_bancarios' => 'array',
        'med_horarios' => 'array',
        'med_dados_bancarios' => 'array',
        'taxa_correio' => 'boolean',
        'med_deslocamento' => 'boolean',
        'med_cobrou_almoco' => 'boolean',
        'med_reembolso_correios' => 'boolean',
        'data_emissao' => 'date',
        'vencimento_original' => 'date',
        'data_entregue_financeiro' => 'date',
        'vencimento_prorrogado' => 'date',
        'aprovado_chefia_em' => 'datetime',
        'confirmado_financeiro_em' => 'datetime',
    ];

    public function notaClientes()
    {
        return $this->hasMany(NotaCliente::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeClinicas($query)
    {
        return $query->where('tipo_nota', 'clinica');
    }

    public function scopeMedicos($query)
    {
        return $query->where('tipo_nota', 'medico');
    }
}

