<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Response;

class CepNotFoundException extends Exception
{
    public function __construct(string $message = 'CEP não encontrado')
    {
        parent::__construct($message, Response::HTTP_NOT_FOUND);
    }
}
