<?php

namespace App\DTOs;

readonly class EnderecoDTO
{
    public function __construct(
        public string $cep,
        public string $logradouro,
        public ?string $complemento,
        public string $bairro,
        public string $cidade,
        public string $uf,
        public ?string $ibge = null
    ) {}

    public static function fromBrasilApi(array $data): self
    {
        return new self(
            cep: self::formatarCep($data['cep'] ?? ''),
            logradouro: $data['street'] ?? '',
            complemento: $data['complement'] ?? null,
            bairro: $data['neighborhood'] ?? '',
            cidade: $data['city'] ?? '',
            uf: $data['state'] ?? '',
            ibge: $data['city_ibge'] ?? null
        );
    }

    private static function formatarCep(string $cep): string
    {
        $cep = preg_replace('/\D/', '', $cep);
        return substr($cep, 0, 5) . '-' . substr($cep, 5, 3);
    }
}
