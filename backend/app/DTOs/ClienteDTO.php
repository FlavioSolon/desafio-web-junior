<?php

namespace App\DTOs;

readonly class ClienteDTO
{
    public function __construct(
        public string $nome,
        public string $cpfCnpj,
        public string $email,
        public string $cep,
        public string $logradouro,
        public string $numero,
        public ?string $complemento,
        public string $bairro,
        public string $cidade,
        public string $uf
    ) {}

    public function toArray(): array
    {
        return [
            'nome' => $this->nome,
            'cpf_cnpj' => $this->limparCpfCnpj($this->cpfCnpj),
            'email' => $this->email,
            'cep' => $this->limparCep($this->cep),
            'logradouro' => $this->logradouro,
            'numero' => $this->numero,
            'complemento' => $this->complemento,
            'bairro' => $this->bairro,
            'cidade' => $this->cidade,
            'uf' => $this->uf,
        ];
    }

    private function limparCpfCnpj(string $valor): string
    {
        return preg_replace('/\D/', '', $valor);
    }

    private function limparCep(string $cep): string
    {
        return preg_replace('/\D/', '', $cep);
    }
}
