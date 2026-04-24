<?php

namespace App\Services;

use App\DTOs\ClienteDTO;
use App\Exceptions\DuplicateClientException;
use App\Models\Cliente;
use App\Repositories\ClienteRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ClientService
{
    public function __construct(
        private ClienteRepository $clienteRepository
    ) {}

    public function criarCliente(ClienteDTO $dto): Cliente
    {
        $cpfCnpj = $this->limparCpfCnpj($dto->cpfCnpj);

        if ($this->clienteRepository->findByCpfCnpj($cpfCnpj)) {
            throw new DuplicateClientException('CPF/CNPJ já cadastrado');
        }

        if ($this->clienteRepository->findByEmail($dto->email)) {
            throw new DuplicateClientException('E-mail já cadastrado');
        }

        return $this->clienteRepository->create($dto->toArray());
    }

    public function atualizarCliente(int $id, ClienteDTO $dto): Cliente
    {
        $cliente = $this->clienteRepository->findById($id);

        return $this->clienteRepository->update($cliente, $dto->toArray());
    }

    public function excluirCliente(int $id): bool
    {
        $cliente = $this->clienteRepository->findById($id);

        return $this->clienteRepository->delete($cliente);
    }

    public function listarClientes(int $perPage = 10, ?string $search = null)
    {
        return $this->clienteRepository->paginate($perPage, $search);
    }

    public function obterClientePorId(int $id): Cliente
    {
        return $this->clienteRepository->findById($id);
    }

    private function limparCpfCnpj(string $valor): string
    {
        return preg_replace('/\D/', '', $valor);
    }
}
