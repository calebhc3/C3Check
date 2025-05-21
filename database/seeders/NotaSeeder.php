<?php

namespace Database\Seeders;

use App\Models\Nota;
use App\Models\User;
use Illuminate\Database\Seeder;

class NotaSeeder extends Seeder
{
public function run(): void
{
    $user = User::first() ?? User::factory()->create();

    Nota::factory(10)->create([
        'user_id' => $user->id,
    ])->each(function ($nota) {
        $valorTotal = 0;

        $registros = \App\Models\NotaCliente::factory(rand(2, 5))->make();

        $registros->each(function ($registro) use ($nota, &$valorTotal) {
            $nota->registros()->create($registro->toArray());
            $valorTotal += $registro->valor;
        });

        $nota->update(['valor_total' => $valorTotal]);
    });

    $this->command->info('10 notas com registros fake criadas via factory!');
}

}
