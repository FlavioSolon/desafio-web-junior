<?php

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('CepController - Consulta de Endereços', function () {

    beforeEach(function () {
        $this->user = User::create([
            'name' => 'Usuário Teste',
            'email' => 'teste@email.com',
            'password' => bcrypt('senha123'),
        ]);

        $this->token = auth('api')->login($this->user);
    });

    test('deve retornar status 200 e JSON do endereço ao consultar CEP válido', function () {
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
        ]);

        $resposta = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson("/api/cep/{$cep}");

        $resposta->assertStatus(200)
            ->assertJson([
                'cep' => '01001-000',
                'uf' => 'SP',
                'cidade' => 'São Paulo',
                'bairro' => 'Sé',
                'logradouro' => 'Praça da Sé',
            ]);
    });

    test('deve aceitar CEP com máscara na URL', function () {
        $cep = '01001-000';
        $responseMock = [
            'cep' => '01001000',
            'state' => 'SP',
            'city' => 'São Paulo',
            'neighborhood' => 'Sé',
            'street' => 'Praça da Sé',
            'service' => 'viacep',
        ];

        Http::fake([
        ]);

        $resposta = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson("/api/cep/{$cep}");

        $resposta->assertStatus(200);
    });

    test('deve retornar status 404 e mensagem padronizada quando CEP não existir', function () {
        $cep = '00000000';

        Http::fake([
                ['message' => 'CEP não encontrado', 'type' => 'not_found'],
                404
            ),
        ]);

        $resposta = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson("/api/cep/{$cep}");

        $resposta->assertStatus(404)
            ->assertJson([
                'message' => 'CEP não encontrado',
            ]);
    });

    test('deve retornar status 400 quando CEP tiver formato inválido', function () {
        $cep = 'invalido';

        $resposta = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson("/api/cep/{$cep}");

        $resposta->assertStatus(400)
            ->assertJson([
                'message' => 'CEP deve conter 8 dígitos numéricos',
            ]);
    });

    test('deve retornar status 400 quando CEP tiver menos de 8 dígitos', function () {
        $cep = '01001';

        $resposta = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson("/api/cep/{$cep}");

        $resposta->assertStatus(400)
            ->assertJson([
                'message' => 'CEP deve conter 8 dígitos numéricos',
            ]);
    });

    test('deve retornar status 400 quando CEP tiver mais de 8 dígitos', function () {
        $cep = '010010001';

        $resposta = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson("/api/cep/{$cep}");

        $resposta->assertStatus(400)
            ->assertJson([
                'message' => 'CEP deve conter 8 dígitos numéricos',
            ]);
    });

    test('deve retornar status 502 quando serviço externo estiver indisponível', function () {
        $cep = '01001000';

        Http::fake([
                throw new \Illuminate\Http\Client\ConnectionException(
                    'cURL error 28: Connection timed out'
                );
            },
        ]);

        $resposta = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson("/api/cep/{$cep}");

        $resposta->assertStatus(502)
            ->assertJson([
                'message' => 'Serviço de consulta de CEP indisponível',
            ]);
    });

    test('deve retornar status 500 quando serviço externo retornar erro inesperado', function () {
        $cep = '01001000';

        Http::fake([
                ['message' => 'Erro interno'],
                500
            ),
        ]);

        $resposta = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson("/api/cep/{$cep}");

        $resposta->assertStatus(502)
            ->assertJson([
                'message' => 'Erro ao consultar serviço de CEP',
            ]);
    });
});

test('deve retornar status 401 quando não estiver autenticado', function () {
    $cep = '01001000';

    $resposta = $this->getJson("/api/cep/{$cep}");

    $resposta->assertStatus(401);
});
