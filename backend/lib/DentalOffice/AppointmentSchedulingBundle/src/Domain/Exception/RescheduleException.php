<?php

namespace DentalOffice\AppointmentSchedulingBundle\Domain\Exception;

use DomainException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

final class RescheduleException extends HttpException
{
    private function __construct(
        string $currentStatus,
        string $message = '',
        int $code = 0
    ) {
        $detail = sprintf(
            'Cannot reschedule appointment with status "%s". Only SCHEDULED or CONFIRMED allowed.',
            $currentStatus
        );

        $headers = [
            'Content-Type' => 'application/problem+json',
        ];

        $problem = [
            'type' => 'https://example.com/probs/reschedule-error',
            'title' => 'Conflict',
            'status' => 409,
            'detail' => $detail,
        ];

        parent::__construct(409, json_encode($problem, JSON_THROW_ON_ERROR), null, $headers);
    }

    public static function invalidStatus(string $currentStatus): self
    {
        return new self($currentStatus);
    }
}