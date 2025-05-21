<?

namespace Database\Factories;

use App\Models\Nota;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotaFactory extends Factory
{
    protected $model = Nota::class;

    public function definition(): array
    {
        return [
            'numero_nf' => 'NF' . $this->faker->unique()->numerify('###'),
            'prestador' => $this->faker->company,
            'valor_total' => 0, // Será calculado após criar os registros
            'data_emissao' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'vencimento_original' => now()->addDays(rand(1, 15)),
            'vencimento_prorrogado' => null,
            'tipo_pagamento' => $this->faker->randomElement(['boleto', 'pix', 'deposito']),
            'dados_bancarios' => $this->faker->iban(),
            'arquivo_nf' => null,
            'user_id' => User::inRandomOrder()->first()->id ?? User::factory(),
        ];
    }
}