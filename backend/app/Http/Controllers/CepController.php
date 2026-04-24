<?php

namespace App\Http\Controllers;

use App\Exceptions\CepNotFoundException;
use App\Exceptions\ExternalServiceException;
use App\Services\BrasilApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class CepController extends Controller
{
    public function __construct(
        private BrasilApiService $brasilApiService
    ) {}

    public function show(string $cep): JsonResponse
    {
        if (!$this->validarCep($cep)) {
            $cepLimpo = preg_replace('/\D/', '', $cep);
            $message = strlen($cepLimpo) !== 8
                ? 'CEP deve conter 8 dígitos numéricos'
                : 'Formato de CEP inválido';

            return response()->json(
                ['message' => $message],
                Response::HTTP_BAD_REQUEST
            );
        }

        try {
            $endereco = $this->brasilApiService->buscarEnderecoPorCep($cep);

            return response()->json([
                'cep' => $endereco->cep,
                'logradouro' => $endereco->logradouro,
                'complemento' => $endereco->complemento,
                'bairro' => $endereco->bairro,
                'cidade' => $endereco->cidade,
                'uf' => $endereco->uf,
                'ibge' => $endereco->ibge,
            ]);
        } catch (CepNotFoundException $e) {
            return response()->json(
                ['message' => $e->getMessage()],
                Response::HTTP_NOT_FOUND
            );
        } catch (ExternalServiceException $e) {
            return response()->json(
                ['message' => $e->getMessage()],
                Response::HTTP_BAD_GATEWAY
            );
        }
    }

    private function validarCep(string $cep): bool
    {
        $cepLimpo = preg_replace('/\D/', '', $cep);
        return strlen($cepLimpo) === 8;
    }
}
