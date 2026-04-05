<?php


namespace DentalOffice\SharedBundle\Domain\Exception;

abstract class DomainException extends \RuntimeException
{
    public function __construct(
        string $message,
        private readonly string $errorCode = 'DOMAIN_ERROR',
        private readonly int $statusCode = 400
    ) {
        parent::__construct($message);
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}