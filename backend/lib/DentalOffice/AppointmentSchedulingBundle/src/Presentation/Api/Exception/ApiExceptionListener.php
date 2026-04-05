<?php

namespace DentalOffice\AppointmentSchedulingBundle\Presentation\Api\Exception;

use DentalOffice\AppointmentSchedulingBundle\Domain\Exception\PatientNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

final class ApiExceptionListener
{
    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        // 🎯 -------- DOMAIN → HTTP --------
        if ($exception instanceof PatientNotFoundException) {
            $event->setResponse(
                new JsonResponse(
                    [
                        'error' => $exception->getMessage(),
                    ],
                    JsonResponse::HTTP_NOT_FOUND
                )
            );
        }
    }
}