<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class NotaClienteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'cliente_atendido' => $this->faker->company,
            'valor' => $this->faker->randomFloat(2, 100, 2000),
            'observacao' => $this->faker->sentence(),
        ];
    }
}