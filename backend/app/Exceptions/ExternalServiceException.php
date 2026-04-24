<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Response;

class ExternalServiceException extends Exception
{
    public function __construct(string $message = 'Erro ao consultar serviço externo')
    {
        parent::__construct($message, Response::HTTP_BAD_GATEWAY);
    }
}
