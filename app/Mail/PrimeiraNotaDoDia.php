<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PrimeiraNotaDoDia extends Mailable
{
    use Queueable, SerializesModels;

    public $dados;

    public function __construct($dados)
    {
        $this->dados = $dados;
    }

    public function build()
    {
        return $this->subject('Primeira Nota do Dia Cadastrada - ' . config('app.name'))
                    ->markdown('emails.primeira_nota_do_dia')
                    ->with('dados', $this->dados);
    }
}