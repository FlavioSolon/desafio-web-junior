<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Autenticação JWT', function () {

    beforeEach(function () {
        // Criar usuário de teste
        $this->user = User::create([
            'name' => 'Usuário Teste',
            'email' => 'teste@email.com',
            'password' => bcrypt('senha123'),
        ]);
    });

    test('deve retornar token JWT válido e status 200 ao fazer login com credenciais corretas', function () {
        // Arrange
        $credentials = [
            'email' => 'teste@email.com',
            'password' => 'senha123',
        ];

        // Act
        $resposta = $this->postJson('/api/auth/login', $credentials);

        // Assert
        $resposta->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'expires_in',
            ])
            ->assertJson([
                'token_type' => 'bearer',
            ]);

        expect($resposta->json('access_token'))->not->toBeNull();
        expect($resposta->json('expires_in'))->toBeGreaterThan(0);
    });

    test('deve retornar status 401 com credenciais inválidas', function () {
        // Arrange
        $credentials = [
            'email' => 'teste@email.com',
            'password' => 'senha_errada',
        ];

        // Act
        $resposta = $this->postJson('/api/auth/login', $credentials);

        // Assert
        $resposta->assertStatus(401)
            ->assertJson([
                'message' => 'Credenciais inválidas',
            ]);
    });

    test('deve retornar status 401 quando e-mail não existir', function () {
        // Arrange
        $credentials = [
            'email' => 'naoexiste@email.com',
            'password' => 'senha123',
        ];

        // Act
        $resposta = $this->postJson('/api/auth/login', $credentials);

        // Assert
        $resposta->assertStatus(401)
            ->assertJson([
                'message' => 'Credenciais inválidas',
            ]);
    });

    test('deve retornar dados do usuário autenticado ao acessar /me', function () {
        // Arrange
        $token = auth('api')->login($this->user);

        // Act
        $resposta = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/auth/me');

        // Assert
        $resposta->assertStatus(200)
            ->assertJson([
                'id' => $this->user->id,
                'name' => 'Usuário Teste',
                'email' => 'teste@email.com',
            ]);
    });

    test('deve retornar status 401 ao acessar /me sem token', function () {
        // Act
        $resposta = $this->getJson('/api/auth/me');

        // Assert
        $resposta->assertStatus(401);
    });

    test('deve retornar status 401 ao acessar /me com token inválido', function () {
        // Act
        $resposta = $this->withHeader('Authorization', 'Bearer token_invalido')
            ->getJson('/api/auth/me');

        // Assert
        $resposta->assertStatus(401);
    });

    test('deve fazer logout com sucesso e invalidar o token', function () {
        // Arrange
        $token = auth('api')->login($this->user);

        // Act
        $resposta = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/auth/logout');

        // Assert
        $resposta->assertStatus(200)
            ->assertJson([
                'message' => 'Logout realizado com sucesso',
            ]);

        // Tentar usar o token novamente
        $respostaAposLogout = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/auth/me');

        $respostaAposLogout->assertStatus(401);
    });

    test('deve retornar status 422 quando e-mail não for fornecido no login', function () {
        // Arrange
        $credentials = [
            'password' => 'senha123',
        ];

        // Act
        $resposta = $this->postJson('/api/auth/login', $credentials);

        // Assert
        $resposta->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });

    test('deve retornar status 422 quando senha não for fornecida no login', function () {
        // Arrange
        $credentials = [
            'email' => 'teste@email.com',
        ];

        // Act
        $resposta = $this->postJson('/api/auth/login', $credentials);

        // Assert
        $resposta->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    });

    test('deve atualizar token com refresh', function () {
        // Arrange
        $token = auth('api')->login($this->user);

        // Act
        $resposta = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/auth/refresh');

        // Assert
        $resposta->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'expires_in',
            ]);
    });
});
