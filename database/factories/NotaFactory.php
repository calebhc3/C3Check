<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Nota;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class NotaFactory extends Factory
{
    protected $model = Nota::class;

    public function definition(): array
    {
        $user = User::factory()->create();
        
        // Campos comuns a todos os tipos
        $base = [
            'aprovado_chefia_em' => null,
            'numero_nf' => 'NF' . $this->faker->unique()->numerify('###'),
            'cnpj' => $this->faker->cnpj(),
            'data_emissao' => now(),
            'data_entregue_financeiro' => now(),
            'vencimento_original' => now()->addDays(10),
            'vencimento_prorrogado' => now()->addDays(20),
            'mes' => now()->format('m/Y'),
            'tipo_pagamento' => $this->faker->randomElement(['boleto', 'deposito', 'pix']),
            'dados_bancarios' => 'Banco: 0001 / Conta: 12345-6',
            'taxa_correio' => $this->faker->boolean,
            'valor_taxa_correio' => $this->faker->randomFloat(2, 0, 50),
            'cidade' => $this->faker->city,
            'estado' => $this->faker->stateAbbr,
            'regiao' => $this->faker->randomElement(['Norte', 'Nordeste', 'Centro-Oeste', 'Sudeste', 'Sul']),
            'observacao' => $this->faker->sentence,
            'arquivo_nf' => null,
            'status' => 'lancada',
            'user_id' => $user->id,
        ];

        return $base;
    }

    public function clinica()
    {
        return $this->state(function (array $attributes) {
            $clientes = [
                ['cliente_atendido' => $this->faker->company, 'valor' => $this->faker->randomFloat(2, 50, 500)],
                ['cliente_atendido' => $this->faker->company, 'valor' => $this->faker->randomFloat(2, 50, 500)],
            ];

            $valorClientes = collect($clientes)->sum('valor');
            $valorTotal = $valorClientes + ($attributes['taxa_correio'] ? $attributes['valor_taxa_correio'] : 0);

            return [
                'tipo_nota' => 'clinica',
                'prestador' => $this->faker->company,
                'clientes' => $clientes,
                'valor_total' => $valorTotal,
                'glosar' => $this->faker->boolean(20), // 20% de chance de estar glosada
                'glosa_valor' => function(array $attributes) {
                    return $attributes['glosar'] ? $this->faker->randomFloat(2, 0, $attributes['valor_total'] * 0.3) : 0;
                },
                'glosa_motivo' => function(array $attributes) {
                    return $attributes['glosar'] ? $this->faker->sentence : null;
                },
            ];
        });
    }

    public function medico()
    {
        return $this->state(function (array $attributes) {
            $horarios = [
                [
                    'data' => now()->format('Y-m-d'),
                    'entrada' => '08:00',
                    'saida_almoco' => '12:00',
                    'retorno_almoco' => '13:00',
                    'saida' => '17:00',
                    'valor_hora' => $this->faker->numberBetween(50, 200),
                    'total' => $this->faker->numberBetween(400, 800),
                ]
            ];

            $totalHoras = collect($horarios)->sum('total');
            $deslocamento = $this->faker->boolean;
            $almoco = $this->faker->boolean;
            $correios = $this->faker->boolean;

            return [
                'tipo_nota' => 'medico',
                'med_nome' => 'Dr. ' . $this->faker->name,
                'med_crm' => 'CRM/' . $this->faker->stateAbbr . ' ' . $this->faker->numerify('######'),
                'med_telefone' => $this->faker->phoneNumber,
                'med_email' => $this->faker->safeEmail,
                'med_cliente_atendido' => $this->faker->company,
                'med_local' => $this->faker->city,
                'med_horarios' => json_encode($horarios),
                'med_valor_total_final' => $totalHoras,
                'med_deslocamento' => $deslocamento,
                'med_valor_deslocamento' => $deslocamento ? $this->faker->randomFloat(2, 30, 100) : 0,
                'med_cobrou_almoco' => $almoco,
                'med_valor_almoco' => $almoco ? $this->faker->randomFloat(2, 20, 50) : 0,
                'med_reembolso_correios' => $correios,
                'med_valor_correios' => $correios ? $this->faker->randomFloat(2, 10, 40) : 0,
                'med_dados_bancarios' => json_encode([
                    'banco' => $this->faker->randomElement(['Itaú', 'Bradesco', 'Santander', 'Banco do Brasil']),
                    'agencia' => $this->faker->numerify('####'),
                    'conta' => $this->faker->numerify('#####-#'),
                    'titular' => $this->faker->name,
                ]),
                'valor_total' => $totalHoras + 
                    ($deslocamento ? $attributes['med_valor_deslocamento'] : 0) + 
                    ($almoco ? $attributes['med_valor_almoco'] : 0) + 
                    ($correios ? $attributes['med_valor_correios'] : 0),
            ];
        });
    }

    public function prestador()
    {
        return $this->state(function (array $attributes) {
            return [
                'tipo_nota' => 'prestador',
                'prestador' => $this->faker->company,
                'valor_total' => $this->faker->randomFloat(2, 100, 1000),
                'glosar' => $this->faker->boolean(20),
                'glosa_valor' => function(array $attributes) {
                    return $attributes['glosar'] ? $this->faker->randomFloat(2, 0, $attributes['valor_total'] * 0.3) : 0;
                },
                'glosa_motivo' => function(array $attributes) {
                    return $attributes['glosar'] ? $this->faker->sentence : null;
                },
            ];
        });
    }

    public function configure()
    {
        return $this->afterCreating(function (Nota $nota) {
            if ($nota->tipo_nota === 'clinica' && isset($nota->clientes)) {
                foreach ($nota->clientes as $cliente) {
                    $nota->notaClientes()->create([
                        'cliente_atendido' => $cliente['cliente_atendido'],
                        'valor' => $cliente['valor'],
                        'observacao' => $this->faker->optional()->sentence,
                    ]);
                }
            }
        });
    }
    public function withFile($filePath)
    {
        return $this->state(function (array $attributes) use ($filePath) {
            return [
                'arquivo_nf' => $filePath,
            ];
        });
    }
    public function withStatus($status)
    {
        return $this->state(function (array $attributes) use ($status) {
            return [
                'status' => $status,
            ];
        });
    }
}