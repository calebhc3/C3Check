<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class UserRoleSeeder extends Seeder
{
    public function run()
    {
        // Criar roles
        $roles = ['contas', 'chefia', 'financeiro'];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // Criar usuários e atribuir roles
        $karina = User::firstOrCreate([
            'email' => 'karina@c3check.com'
        ], [
            'name' => 'Karina',
            'password' => bcrypt('password123'),
        ]);
        $karina->assignRole('contas');

        $ana = User::firstOrCreate([
            'email' => 'ana@c3check.com'
        ], [
            'name' => 'Ana',
            'password' => bcrypt('password123'),
        ]);
        $ana->assignRole('chefia');

        $rickelme = User::firstOrCreate([
            'email' => 'rickelme@c3check.com'
        ], [
            'name' => 'Rickelme',
            'password' => bcrypt('password123'),
        ]);
        $rickelme->assignRole('financeiro');
    }
}

