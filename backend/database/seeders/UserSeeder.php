<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Teste Fametro',
            'email' => 'teste@famtetro.edu.br',
            'password' => 'senha123',
        ]);
    }
}
