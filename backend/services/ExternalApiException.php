<?php

class ExternalApiException extends RuntimeException
{
    private int $httpStatus;
    private string $errorCode;

    public function __construct(string $message, int $httpStatus, string $errorCode)
    {
        parent::__construct($message);
        $this->httpStatus = $httpStatus;
        $this->errorCode = $errorCode;
    }

    public function getHttpStatus(): int
    {
        return $this->httpStatus;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }
}
