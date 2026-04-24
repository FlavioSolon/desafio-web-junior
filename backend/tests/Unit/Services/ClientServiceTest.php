<?php

use App\DTOs\ClienteDTO;
use App\Exceptions\DuplicateClientException;
use App\Models\Cliente;
use App\Repositories\ClienteRepository;
use App\Services\ClientService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

beforeEach(function () {
    $this->clienteRepository = Mockery::mock(ClienteRepository::class);
    $this->service = new ClientService($this->clienteRepository);
});

afterEach(function () {
    Mockery::close();
});

describe('ClientService', function () {

    test('deve criar um cliente com sucesso usando ClientDTO válido', function () {
        $dto = new ClienteDTO(
            nome: 'João Silva',
            cpfCnpj: '123.456.789-00',
            email: 'joao@email.com',
            cep: '01001-000',
            logradouro: 'Praça da Sé',
            numero: '100',
            complemento: 'Apto 10',
            bairro: 'Sé',
            cidade: 'São Paulo',
            uf: 'SP'
        );

        $clienteMock = Mockery::mock(Cliente::class);
        $clienteMock->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $clienteMock->shouldReceive('getAttribute')->with('nome')->andReturn('João Silva');
        $clienteMock->shouldReceive('getAttribute')->with('cpf_cnpj')->andReturn('12345678900');

        $this->clienteRepository
            ->shouldReceive('findByCpfCnpj')
            ->with('12345678900')
            ->once()
            ->andReturnNull();

        $this->clienteRepository
            ->shouldReceive('findByEmail')
            ->with('joao@email.com')
            ->once()
            ->andReturnNull();

        $this->clienteRepository
            ->shouldReceive('create')
            ->once()
            ->andReturn($clienteMock);

        $resultado = $this->service->criarCliente($dto);

        expect($resultado)->toBe($clienteMock);
    });

    test('deve lançar exceção ao tentar criar cliente com CPF/CNPJ já existente', function () {
        $dto = new ClienteDTO(
            nome: 'João Silva',
            cpfCnpj: '123.456.789-00',
            email: 'joao@email.com',
            cep: '01001-000',
            logradouro: 'Praça da Sé',
            numero: '100',
            complemento: null,
            bairro: 'Sé',
            cidade: 'São Paulo',
            uf: 'SP'
        );

        $clienteExistente = Mockery::mock(Cliente::class);

        $this->clienteRepository
            ->shouldReceive('findByCpfCnpj')
            ->with('12345678900')
            ->once()
            ->andReturn($clienteExistente);

        expect(fn () => $this->service->criarCliente($dto))
            ->toThrow(DuplicateClientException::class, 'CPF/CNPJ já cadastrado');
    });

    test('deve lançar exceção ao tentar criar cliente com e-mail já existente', function () {
        $dto = new ClienteDTO(
            nome: 'João Silva',
            cpfCnpj: '123.456.789-00',
            email: 'joao@email.com',
            cep: '01001-000',
            logradouro: 'Praça da Sé',
            numero: '100',
            complemento: null,
            bairro: 'Sé',
            cidade: 'São Paulo',
            uf: 'SP'
        );

        $this->clienteRepository
            ->shouldReceive('findByCpfCnpj')
            ->with('12345678900')
            ->once()
            ->andReturnNull();

        $this->clienteRepository
            ->shouldReceive('findByEmail')
            ->with('joao@email.com')
            ->once()
            ->andReturn(Mockery::mock(Cliente::class));

        expect(fn () => $this->service->criarCliente($dto))
            ->toThrow(DuplicateClientException::class, 'E-mail já cadastrado');
    });

    test('deve formatar e remover máscara do CPF antes de salvar', function () {
        $dto = new ClienteDTO(
            nome: 'Maria Santos',
            cpfCnpj: '987.654.321-00',
            email: 'maria@email.com',
            cep: '20031-170',
            logradouro: 'Avenida Rio Branco',
            numero: '50',
            complemento: 'Sala 200',
            bairro: 'Centro',
            cidade: 'Rio de Janeiro',
            uf: 'RJ'
        );

        $this->clienteRepository
            ->shouldReceive('findByCpfCnpj')
            ->with('98765432100')
            ->once()
            ->andReturnNull();

        $this->clienteRepository
            ->shouldReceive('findByEmail')
            ->once()
            ->andReturnNull();

        $this->clienteRepository
            ->shouldReceive('create')
            ->with(Mockery::on(function ($data) {
                return $data['cpf_cnpj'] === '98765432100'
                    && $data['cep'] === '20031170';
            }))
            ->once()
            ->andReturn(Mockery::mock(Cliente::class));

        $this->service->criarCliente($dto);

        expect(true)->toBeTrue();
    });

    test('deve formatar e remover máscara do CNPJ antes de salvar', function () {
        $dto = new ClienteDTO(
            nome: 'Empresa XYZ Ltda',
            cpfCnpj: '11.222.333/0001-44',
            email: 'empresa@xyz.com',
            cep: '01310-100',
            logradouro: 'Avenida Paulista',
            numero: '1000',
            complemento: 'Andar 15',
            bairro: 'Bela Vista',
            cidade: 'São Paulo',
            uf: 'SP'
        );

        $this->clienteRepository
            ->shouldReceive('findByCpfCnpj')
            ->with('11222333000144')
            ->once()
            ->andReturnNull();

        $this->clienteRepository
            ->shouldReceive('findByEmail')
            ->once()
            ->andReturnNull();

        $this->clienteRepository
            ->shouldReceive('create')
            ->with(Mockery::on(function ($data) {
                return $data['cpf_cnpj'] === '11222333000144';
            }))
            ->once()
            ->andReturn(Mockery::mock(Cliente::class));

        $this->service->criarCliente($dto);

        expect(true)->toBeTrue();
    });

    test('deve atualizar cliente existente com sucesso', function () {
        $clienteId = 1;
        $dto = new ClienteDTO(
            nome: 'João Silva Atualizado',
            cpfCnpj: '123.456.789-00',
            email: 'joao.novo@email.com',
            cep: '01001-000',
            logradouro: 'Rua Nova',
            numero: '200',
            complemento: null,
            bairro: 'Centro',
            cidade: 'São Paulo',
            uf: 'SP'
        );

        $clienteMock = Mockery::mock(Cliente::class);

        $this->clienteRepository
            ->shouldReceive('findById')
            ->with($clienteId)
            ->once()
            ->andReturn($clienteMock);

        $this->clienteRepository
            ->shouldReceive('update')
            ->with($clienteMock, Mockery::any())
            ->once()
            ->andReturn($clienteMock);

        $resultado = $this->service->atualizarCliente($clienteId, $dto);

        expect($resultado)->toBe($clienteMock);
    });

    test('deve lançar exceção ao tentar atualizar cliente inexistente', function () {
        $clienteId = 999;
        $dto = new ClienteDTO(
            nome: 'Cliente Inexistente',
            cpfCnpj: '123.456.789-00',
            email: 'teste@email.com',
            cep: '01001-000',
            logradouro: 'Rua Teste',
            numero: '100',
            complemento: null,
            bairro: 'Centro',
            cidade: 'São Paulo',
            uf: 'SP'
        );

        $this->clienteRepository
            ->shouldReceive('findById')
            ->with($clienteId)
            ->once()
            ->andThrow(new ModelNotFoundException());

        expect(fn () => $this->service->atualizarCliente($clienteId, $dto))
            ->toThrow(ModelNotFoundException::class);
    });

    test('deve excluir cliente existente com sucesso', function () {
        $clienteId = 1;
        $clienteMock = Mockery::mock(Cliente::class);

        $this->clienteRepository
            ->shouldReceive('findById')
            ->with($clienteId)
            ->once()
            ->andReturn($clienteMock);

        $this->clienteRepository
            ->shouldReceive('delete')
            ->with($clienteMock)
            ->once()
            ->andReturnTrue();

        $resultado = $this->service->excluirCliente($clienteId);

        expect($resultado)->toBeTrue();
    });
});
