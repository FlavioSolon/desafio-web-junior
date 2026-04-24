<?php

use App\Models\Cliente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('deve retornar status 401 quando não estiver autenticado', function () {
    // Act
    $resposta = $this->getJson('/api/clientes');

    // Assert
    $resposta->assertStatus(401);
});

describe('ClientController - CRUD de Clientes', function () {

    beforeEach(function () {
        // Criar usuário e autenticar
        $this->user = User::create([
            'name' => 'Usuário Teste',
            'email' => 'teste@email.com',
            'password' => bcrypt('senha123'),
        ]);

        $this->token = auth('api')->login($this->user);
    });

    describe('Listagem de Clientes', function () {

        test('deve retornar status 200 e lista paginada de clientes', function () {
            // Arrange
            Cliente::insert([
                [
                    'nome' => 'Cliente 1',
                    'cpf_cnpj' => '12345678900',
                    'email' => 'cliente1@email.com',
                    'cep' => '01001000',
                    'logradouro' => 'Rua 1',
                    'numero' => '100',
                    'bairro' => 'Centro',
                    'cidade' => 'São Paulo',
                    'uf' => 'SP',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'nome' => 'Cliente 2',
                    'cpf_cnpj' => '98765432100',
                    'email' => 'cliente2@email.com',
                    'cep' => '20031170',
                    'logradouro' => 'Rua 2',
                    'numero' => '200',
                    'bairro' => 'Centro',
                    'cidade' => 'Rio de Janeiro',
                    'uf' => 'RJ',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);

            // Act
            $resposta = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                ->getJson('/api/clientes');

            // Assert
            $resposta->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'nome',
                            'cpf_cnpj',
                            'email',
                            'cep',
                            'logradouro',
                            'numero',
                            'complemento',
                            'bairro',
                            'cidade',
                            'uf',
                            'created_at',
                            'updated_at',
                        ],
                    ],
                    'links',
                    'meta',
                ]);

            expect($resposta->json('data'))->toHaveCount(2);
        });

        test('deve retornar lista vazia quando não houver clientes', function () {
            // Act
            $resposta = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                ->getJson('/api/clientes');

            // Assert
            $resposta->assertStatus(200);
            expect($resposta->json('data'))->toBeEmpty();
        });

        test('deve paginar resultados corretamente', function () {
            // Arrange - Criar 15 clientes
            for ($i = 1; $i <= 15; $i++) {
                Cliente::create([
                    'nome' => "Cliente {$i}",
                    'cpf_cnpj' => str_pad((string)$i, 11, '0', STR_PAD_LEFT),
                    'email' => "cliente{$i}@email.com",
                    'cep' => '01001000',
                    'logradouro' => 'Rua Teste',
                    'numero' => '100',
                    'bairro' => 'Centro',
                    'cidade' => 'São Paulo',
                    'uf' => 'SP',
                ]);
            }

            // Act - Página 1
            $resposta = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                ->getJson('/api/clientes?page=1&per_page=10');

            // Assert
            $resposta->assertStatus(200);
            expect($resposta->json('data'))->toHaveCount(10);
            expect($resposta->json('meta.current_page'))->toBe(1);
            expect($resposta->json('meta.last_page'))->toBe(2);
        });
    });

    describe('Criação de Clientes', function () {

        test('deve retornar status 201 ao cadastrar cliente com payload válido', function () {
            // Arrange
            $payload = [
                'nome' => 'Novo Cliente',
                'cpf_cnpj' => '12345678900',
                'email' => 'novo@email.com',
                'cep' => '01001-000',
                'logradouro' => 'Praça da Sé',
                'numero' => '100',
                'complemento' => 'Apto 10',
                'bairro' => 'Sé',
                'cidade' => 'São Paulo',
                'uf' => 'SP',
            ];

            // Act
            $resposta = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                ->postJson('/api/clientes', $payload);

            // Assert
            $resposta->assertStatus(201)
                ->assertJson([
                    'message' => 'Cliente criado com sucesso',
                ])
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'nome',
                        'cpf_cnpj',
                        'email',
                        'created_at',
                    ],
                ]);

            // Verificar se foi salvo no banco
            $this->assertDatabaseHas('clientes', [
                'nome' => 'Novo Cliente',
                'email' => 'novo@email.com',
                'cpf_cnpj' => '12345678900',
            ]);
        });

        test('deve retornar status 422 quando e-mail for inválido', function () {
            // Arrange
            $payload = [
                'nome' => 'Novo Cliente',
                'cpf_cnpj' => '12345678900',
                'email' => 'email_invalido',
                'cep' => '01001-000',
                'logradouro' => 'Rua Teste',
                'numero' => '100',
                'bairro' => 'Centro',
                'cidade' => 'São Paulo',
                'uf' => 'SP',
            ];

            // Act
            $resposta = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                ->postJson('/api/clientes', $payload);

            // Assert
            $resposta->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
        });

        test('deve retornar status 422 quando CPF/CNPJ não for fornecido', function () {
            // Arrange
            $payload = [
                'nome' => 'Novo Cliente',
                'email' => 'novo@email.com',
                'cep' => '01001-000',
                'logradouro' => 'Rua Teste',
                'numero' => '100',
                'bairro' => 'Centro',
                'cidade' => 'São Paulo',
                'uf' => 'SP',
            ];

            // Act
            $resposta = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                ->postJson('/api/clientes', $payload);

            // Assert
            $resposta->assertStatus(422)
                ->assertJsonValidationErrors(['cpf_cnpj']);
        });

        test('deve retornar status 422 quando nome não for fornecido', function () {
            // Arrange
            $payload = [
                'cpf_cnpj' => '12345678900',
                'email' => 'novo@email.com',
                'cep' => '01001-000',
                'logradouro' => 'Rua Teste',
                'numero' => '100',
                'bairro' => 'Centro',
                'cidade' => 'São Paulo',
                'uf' => 'SP',
            ];

            // Act
            $resposta = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                ->postJson('/api/clientes', $payload);

            // Assert
            $resposta->assertStatus(422)
                ->assertJsonValidationErrors(['nome']);
        });

        test('deve retornar status 409 ao tentar cadastrar CPF duplicado', function () {
            // Arrange
            Cliente::create([
                'nome' => 'Cliente Existente',
                'cpf_cnpj' => '12345678900',
                'email' => 'existente@email.com',
                'cep' => '01001000',
                'logradouro' => 'Rua Teste',
                'numero' => '100',
                'bairro' => 'Centro',
                'cidade' => 'São Paulo',
                'uf' => 'SP',
            ]);

            $payload = [
                'nome' => 'Novo Cliente',
                'cpf_cnpj' => '12345678900',
                'email' => 'novo@email.com',
                'cep' => '01001-000',
                'logradouro' => 'Rua Nova',
                'numero' => '200',
                'bairro' => 'Centro',
                'cidade' => 'São Paulo',
                'uf' => 'SP',
            ];

            // Act
            $resposta = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                ->postJson('/api/clientes', $payload);

            // Assert
            $resposta->assertStatus(409)
                ->assertJson([
                    'message' => 'CPF/CNPJ já cadastrado',
                ]);
        });

        test('deve retornar status 409 ao tentar cadastrar e-mail duplicado', function () {
            // Arrange
            Cliente::create([
                'nome' => 'Cliente Existente',
                'cpf_cnpj' => '12345678900',
                'email' => 'duplicado@email.com',
                'cep' => '01001000',
                'logradouro' => 'Rua Teste',
                'numero' => '100',
                'bairro' => 'Centro',
                'cidade' => 'São Paulo',
                'uf' => 'SP',
            ]);

            $payload = [
                'nome' => 'Novo Cliente',
                'cpf_cnpj' => '98765432100',
                'email' => 'duplicado@email.com',
                'cep' => '01001-000',
                'logradouro' => 'Rua Nova',
                'numero' => '200',
                'bairro' => 'Centro',
                'cidade' => 'São Paulo',
                'uf' => 'SP',
            ];

            // Act
            $resposta = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                ->postJson('/api/clientes', $payload);

            // Assert
            $resposta->assertStatus(409)
                ->assertJson([
                    'message' => 'E-mail já cadastrado',
                ]);
        });
    });

    describe('Edição de Clientes', function () {

        beforeEach(function () {
            $this->cliente = Cliente::create([
                'nome' => 'Cliente Original',
                'cpf_cnpj' => '12345678900',
                'email' => 'original@email.com',
                'cep' => '01001000',
                'logradouro' => 'Rua Original',
                'numero' => '100',
                'bairro' => 'Centro',
                'cidade' => 'São Paulo',
                'uf' => 'SP',
            ]);
        });

        test('deve atualizar dados do cliente e retornar status 200', function () {
            // Arrange
            $payload = [
                'nome' => 'Cliente Atualizado',
                'cpf_cnpj' => '12345678900',
                'email' => 'atualizado@email.com',
                'cep' => '20031170',
                'logradouro' => 'Avenida Rio Branco',
                'numero' => '50',
                'complemento' => 'Sala 100',
                'bairro' => 'Centro',
                'cidade' => 'Rio de Janeiro',
                'uf' => 'RJ',
            ];

            // Act
            $resposta = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                ->putJson("/api/clientes/{$this->cliente->id}", $payload);

            // Assert
            $resposta->assertStatus(200)
                ->assertJson([
                    'message' => 'Cliente atualizado com sucesso',
                ]);

            $this->assertDatabaseHas('clientes', [
                'id' => $this->cliente->id,
                'nome' => 'Cliente Atualizado',
                'email' => 'atualizado@email.com',
            ]);
        });

        test('deve retornar status 404 ao tentar atualizar cliente inexistente', function () {
            // Arrange
            $payload = [
                'nome' => 'Cliente',
                'cpf_cnpj' => '12345678900',
                'email' => 'teste@email.com',
                'cep' => '01001-000',
                'logradouro' => 'Rua Teste',
                'numero' => '100',
                'bairro' => 'Centro',
                'cidade' => 'São Paulo',
                'uf' => 'SP',
            ];

            // Act
            $resposta = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                ->putJson('/api/clientes/9999', $payload);

            // Assert
            $resposta->assertStatus(404);
        });
    });

    describe('Exclusão de Clientes', function () {

        test('deve excluir cliente e retornar status 204', function () {
            // Arrange
            $cliente = Cliente::create([
                'nome' => 'Cliente para Excluir',
                'cpf_cnpj' => '12345678900',
                'email' => 'excluir@email.com',
                'cep' => '01001000',
                'logradouro' => 'Rua Teste',
                'numero' => '100',
                'bairro' => 'Centro',
                'cidade' => 'São Paulo',
                'uf' => 'SP',
            ]);

            // Act
            $resposta = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                ->deleteJson("/api/clientes/{$cliente->id}");

            // Assert
            $resposta->assertStatus(204);
            $this->assertDatabaseMissing('clientes', ['id' => $cliente->id]);
        });

        test('deve retornar status 404 ao tentar excluir cliente inexistente', function () {
            // Act
            $resposta = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                ->deleteJson('/api/clientes/9999');

            // Assert
            $resposta->assertStatus(404);
        });
    });
});
