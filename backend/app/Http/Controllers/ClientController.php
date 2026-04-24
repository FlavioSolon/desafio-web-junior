<?php

namespace App\Http\Controllers;

use App\DTOs\ClienteDTO;
use App\Exceptions\DuplicateClientException;
use App\Services\ClientService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ClientController extends Controller
{
    public function __construct(
        private ClientService $clientService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 10);
        $search = $request->get('search');

        $clientes = $this->clientService->listarClientes($perPage, $search);

        return response()->json([
            'data' => $clientes->items(),
            'links' => [
                'first' => $clientes->url(1),
                'last' => $clientes->url($clientes->lastPage()),
                'prev' => $clientes->previousPageUrl(),
                'next' => $clientes->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => $clientes->currentPage(),
                'last_page' => $clientes->lastPage(),
                'per_page' => $clientes->perPage(),
                'total' => $clientes->total(),
            ]
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'cpf_cnpj' => 'required|string|max:18',
            'email' => 'required|email|max:255',
            'cep' => 'required|string|max:9',
            'logradouro' => 'required|string|max:255',
            'numero' => 'required|string|max:20',
            'complemento' => 'nullable|string|max:255',
            'bairro' => 'required|string|max:255',
            'cidade' => 'required|string|max:255',
            'uf' => 'required|string|size:2',
        ]);

        try {
            $dto = new ClienteDTO(
                nome: $request->input('nome'),
                cpfCnpj: $request->input('cpf_cnpj'),
                email: $request->input('email'),
                cep: $request->input('cep'),
                logradouro: $request->input('logradouro'),
                numero: $request->input('numero'),
                complemento: $request->input('complemento'),
                bairro: $request->input('bairro'),
                cidade: $request->input('cidade'),
                uf: $request->input('uf')
            );

            $cliente = $this->clientService->criarCliente($dto);

            return response()->json([
                'message' => 'Cliente criado com sucesso',
                'data' => $cliente
            ], Response::HTTP_CREATED);
        } catch (DuplicateClientException $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_CONFLICT);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $cliente = $this->clientService->obterClientePorId($id);
            return response()->json($cliente);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Cliente não encontrado'], Response::HTTP_NOT_FOUND);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'cpf_cnpj' => 'required|string|max:18',
            'email' => 'required|email|max:255',
            'cep' => 'required|string|max:9',
            'logradouro' => 'required|string|max:255',
            'numero' => 'required|string|max:20',
            'complemento' => 'nullable|string|max:255',
            'bairro' => 'required|string|max:255',
            'cidade' => 'required|string|max:255',
            'uf' => 'required|string|size:2',
        ]);

        try {
            $dto = new ClienteDTO(
                nome: $request->input('nome'),
                cpfCnpj: $request->input('cpf_cnpj'),
                email: $request->input('email'),
                cep: $request->input('cep'),
                logradouro: $request->input('logradouro'),
                numero: $request->input('numero'),
                complemento: $request->input('complemento'),
                bairro: $request->input('bairro'),
                cidade: $request->input('cidade'),
                uf: $request->input('uf')
            );

            $cliente = $this->clientService->atualizarCliente($id, $dto);

            return response()->json([
                'message' => 'Cliente atualizado com sucesso',
                'data' => $cliente
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Cliente não encontrado'], Response::HTTP_NOT_FOUND);
        } catch (DuplicateClientException $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_CONFLICT);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->clientService->excluirCliente($id);
            return response()->json(null, Response::HTTP_NO_CONTENT);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Cliente não encontrado'], Response::HTTP_NOT_FOUND);
        }
    }
}
