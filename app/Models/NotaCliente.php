<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotaCliente extends Model
{
    use HasFactory;

    protected $fillable = [
        'nota_id',
        'cliente_atendido',
        'valor',
        'observacao',
    ];

    public function nota()
    {
        return $this->belongsTo(Nota::class);
    }
}
