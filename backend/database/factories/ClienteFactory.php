<?php

namespace Database\Factories;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClienteFactory extends Factory
{
    protected $model = Cliente::class;

    public function definition(): array
    {
        return [
            'nome' => fake()->name(),
            'cpf_cnpj' => fake()->unique()->numerify('###########'),
            'email' => fake()->unique()->safeEmail(),
            'cep' => fake()->postcode(),
            'logradouro' => fake()->streetName(),
            'numero' => fake()->buildingNumber(),
            'complemento' => fake()->optional()->secondaryAddress(),
            'bairro' => fake()->citySuffix(),
            'cidade' => fake()->city(),
            'uf' => fake()->stateAbbr(),
        ];
    }
}
