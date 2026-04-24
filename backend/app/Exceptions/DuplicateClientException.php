<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Response;

class DuplicateClientException extends Exception
{
    public function __construct(string $message = 'Cliente já cadastrado')
    {
        parent::__construct($message, Response::HTTP_CONFLICT);
    }
}
