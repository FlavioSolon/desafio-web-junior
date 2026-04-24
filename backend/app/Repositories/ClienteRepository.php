<?php

namespace App\Repositories;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ClienteRepository
{
    public function findById(int $id): ?Cliente
    {
        $cliente = Cliente::find($id);

        if (!$cliente) {
            throw new ModelNotFoundException();
        }

        return $cliente;
    }

    public function findByCpfCnpj(string $cpfCnpj): ?Cliente
    {
        return Cliente::where('cpf_cnpj', $cpfCnpj)->first();
    }

    public function findByEmail(string $email): ?Cliente
    {
        return Cliente::where('email', $email)->first();
    }

    public function create(array $data): Cliente
    {
        return Cliente::create($data);
    }

    public function update(Cliente $cliente, array $data): Cliente
    {
        $cliente->update($data);
        return $cliente->fresh();
    }

    public function delete(Cliente $cliente): bool
    {
        return $cliente->delete();
    }

    public function paginate(int $perPage = 10, ?string $search = null)
    {
        $query = Cliente::query();

        if ($search) {
            $query->where('nome', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('cpf_cnpj', 'like', "%{$search}%");
        }

        return $query->paginate($perPage);
    }
}
