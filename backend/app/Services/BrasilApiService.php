<?php

namespace App\Services;

use App\DTOs\EnderecoDTO;
use App\Exceptions\CepNotFoundException;
use App\Exceptions\ExternalServiceException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Response;

class BrasilApiService
{
    private const BASE_URL = 'https://brasilapi.com.br/api/cep/v2';

    public function buscarEnderecoPorCep(string $cep): EnderecoDTO
    {
        $cep = $this->limparCep($cep);

        try {
            $response = Http::timeout(5)->get(self::BASE_URL . '/' . $cep);

            if ($response->status() === Response::HTTP_NOT_FOUND) {
                throw new CepNotFoundException('CEP não encontrado');
            }

            if ($response->failed()) {
                throw new ExternalServiceException('Erro ao consultar serviço de CEP');
            }

            return EnderecoDTO::fromBrasilApi($response->json());
        } catch (ConnectionException $e) {
            throw new ExternalServiceException('Serviço de consulta de CEP indisponível');
        }
    }

    private function limparCep(string $cep): string
    {
        return preg_replace('/\D/', '', $cep);
    }
}
