<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Nota extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero_nf',
        'prestador',
        'valor_total',
        'data_emissao',
        'vencimento_original',
        'vencimento_prorrogado',
        'tipo_pagamento',
        'dados_bancarios',
        'status',
        'arquivo_nf',
        'user_id',
    ];

    protected $casts = [
        'dados_bancarios' => 'array',
    ];

    public function clientes()
    {
        return $this->hasMany(NotaCliente::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
