<?php

namespace App\Exceptions;

use Exception;

class ApiGatewayException extends Exception
{
    protected $statusCode;

    public function __construct(string $message = "", int $statusCode = 500)
    {
        parent::__construct($message);
        $this->statusCode = $statusCode;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }
}