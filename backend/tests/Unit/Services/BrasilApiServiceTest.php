<?php

use App\DTOs\EnderecoDTO;
use App\Exceptions\CepNotFoundException;
use App\Exceptions\ExternalServiceException;
use App\Services\BrasilApiService;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->service = new BrasilApiService();
});

describe('BrasilApiService', function () {

    test('deve retornar endereço formatado em DTO ao informar CEP válido', function () {
        $cep = '01001000';
        $responseMock = [
            'cep' => '01001000',
            'state' => 'SP',
            'city' => 'São Paulo',
            'neighborhood' => 'Sé',
            'street' => 'Praça da Sé',
            'service' => 'viacep',
        ];

        Http::fake([
            'https://brasilapi.com.br/api/cep/v2/*' => Http::response($responseMock, 200),
        ]);

        $resultado = $this->service->buscarEnderecoPorCep($cep);

        expect($resultado)->toBeInstanceOf(EnderecoDTO::class);
        expect($resultado->cep)->toBe('01001-000');
        expect($resultado->uf)->toBe('SP');
        expect($resultado->cidade)->toBe('São Paulo');
        expect($resultado->bairro)->toBe('Sé');
        expect($resultado->logradouro)->toBe('Praça da Sé');
    });

    test('deve lançar CepNotFoundException quando a BrasilAPI retornar 404', function () {
        $cep = '00000000';

        Http::fake([
            'https://brasilapi.com.br/api/cep/v2/*' => Http::response(
                ['message' => 'CEP não encontrado', 'type' => 'not_found'],
                404
            ),
        ]);

        expect(fn () => $this->service->buscarEnderecoPorCep($cep))
            ->toThrow(CepNotFoundException::class, 'CEP não encontrado');
    });

    test('deve lançar ExternalServiceException quando a BrasilAPI estiver fora do ar (timeout)', function () {
        $cep = '01001000';

        Http::fake([
            'https://brasilapi.com.br/api/cep/v2/*' => function () {
                throw new \Illuminate\Http\Client\ConnectionException(
                    'cURL error 28: Connection timed out'
                );
            },
        ]);

        expect(fn () => $this->service->buscarEnderecoPorCep($cep))
            ->toThrow(ExternalServiceException::class, 'Serviço de consulta de CEP indisponível');
    });

    test('deve lançar ExternalServiceException quando a BrasilAPI retornar erro 500', function () {
        $cep = '01001000';

        Http::fake([
            'https://brasilapi.com.br/api/cep/v2/*' => Http::response(
                ['message' => 'Erro interno do servidor'],
                500
            ),
        ]);

        expect(fn () => $this->service->buscarEnderecoPorCep($cep))
            ->toThrow(ExternalServiceException::class, 'Erro ao consultar serviço de CEP');
    });

    test('deve formatar CEP removendo caracteres não numéricos antes da consulta', function () {
        $cepComMascara = '01001-000';
        $responseMock = [
            'cep' => '01001000',
            'state' => 'SP',
            'city' => 'São Paulo',
            'neighborhood' => 'Sé',
            'street' => 'Praça da Sé',
            'service' => 'viacep',
        ];

        Http::fake([
            'https://brasilapi.com.br/api/cep/v2/01001000' => Http::response($responseMock, 200),
        ]);

        $resultado = $this->service->buscarEnderecoPorCep($cepComMascara);

        expect($resultado->cep)->toBe('01001-000');
        Http::assertSent(function ($request) {
            return $request->url() === 'https://brasilapi.com.br/api/cep/v2/01001000';
        });
    });
});
