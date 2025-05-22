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

        Nota::factory(10)->create(['user_id' => $user->id])->each(function ($nota) {
            // Somente para notas de clínica, cria registros fictícios de clientes vinculados
            if ($nota->tipo_nota === 'clinica') {
                $valorTotal = 0;

                $registros = \App\Models\NotaCliente::factory(rand(2, 5))->make();

                $registros->each(function ($registro) use ($nota, &$valorTotal) {
                $nota->notaClientes()->create($registro->toArray());
                    $valorTotal += $registro->valor;
                });

                $nota->update(['valor_total' => $valorTotal]);
            }
        });

        $this->command->info('Notas fake (clínicas e médicos) criadas com sucesso!');
    }
}
