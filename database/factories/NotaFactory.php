<?php

namespace Database\Factories;

use App\Models\Nota;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotaFactory extends Factory
{
    protected $model = Nota::class;

    public function definition(): array
    {
        $tipoNota = $this->faker->randomElement(['clinica', 'medico']);
        $user = User::inRandomOrder()->first() ?? User::factory()->create();

        $base = [
            'tipo_nota' => $tipoNota,
            'numero_nf' => 'NF' . $this->faker->unique()->numerify('###'),
            'prestador' => $this->faker->company,
            'cnpj' => $this->faker->cnpj(),
            'valor_total' => 0,
            'taxa_correio' => $this->faker->boolean(20),
            'valor_taxa_correio' => $this->faker->randomFloat(2, 0, 50),
            'data_emissao' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'vencimento_original' => $this->faker->dateTimeBetween('now', '+20 days'),
            'data_entregue_financeiro' => $this->faker->boolean(70) ? now() : null,
            'mes' => now()->format('m/Y'),
            'vencimento_prorrogado' => $this->faker->boolean(30) ? now()->addDays(rand(5, 15)) : null,
            'tipo_pagamento' => $this->faker->randomElement(['boleto', 'deposito', 'pix']),
            'dados_bancarios' => json_encode([
                'banco' => 'Nubank',
                'agencia' => '0001',
                'conta' => $this->faker->numerify('#####-#'),
                'titular' => $this->faker->name,
            ]),
            'status' => 'lancada',
            'arquivo_nf' => null,
            'user_id' => $user->id,
        ];

if ($tipoNota === 'medico') {
    $almocoInicio = $this->faker->time('H:i', '13:00'); // até 13h
    $almocoFim = \Carbon\Carbon::createFromFormat('H:i', $almocoInicio)->addMinutes(60)->format('H:i');

    $horarios = [
        [
            'data' => now()->toDateString(),
            'entrada' => '09:00',
            'saida_almoco' => $almocoInicio,
            'retorno_almoco' => $almocoFim,
            'saida' => '13:30',
            'valor_hora' => 110,
            'total' => 495,
        ],
        [
            'data' => now()->toDateString(),
            'entrada' => '14:00',
            'saida_almoco' => '17:00',
            'retorno_almoco' => '18:00',
            'saida' => '19:00',
            'valor_hora' => 110,
            'total' => 440,
        ]
    ];

    $base = array_merge($base, [
        'med_nome' => $this->faker->name('female'),
        'med_telefone' => $this->faker->phoneNumber,
        'med_email' => $this->faker->safeEmail,
        'med_cliente_atendido' => $this->faker->randomElement(['Atendimento Telemedicina', 'Consulta Ocupacional']),
        'med_local' => $this->faker->city,
        'med_horarios' => json_encode($horarios),
        'med_valor_total_final' => collect($horarios)->sum('total'),
        'med_deslocamento' => true,
        'med_valor_deslocamento' => 50,
        'med_cobrou_almoco' => true,
        'med_valor_almoco' => 25,
        'med_reembolso_correios' => true,
        'med_valor_correios' => 30,
        'med_dados_bancarios' => json_encode([
            'banco' => 'Nubank',
            'agencia' => '0001',
            'conta' => $this->faker->numerify('#####-#'),
            'titular' => $this->faker->name,
        ]),
        'valor_total' => collect($horarios)->sum('total') + 25 + 50 + 30,

    ]);
}
        return $base;
    }
}
