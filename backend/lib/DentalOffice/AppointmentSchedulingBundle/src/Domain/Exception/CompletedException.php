<?php


namespace DentalOffice\AppointmentSchedulingBundle\Domain\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class CompletedException extends HttpException
{
     public function __construct(string  $currentStatus, string $message = '', ?Throwable $previous = null, array $headers = [], int $code = 0)
     {
        $detail = sprintf(
            'Cannot complete appointment with status "%s". Only CONFIRMED allowed.',
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




