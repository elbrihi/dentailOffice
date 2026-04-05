<?php


namespace DentalOffice\SharedBundle\Infrastructure\Api\Listener;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

use  DentalOffice\SharedBundle\Domain\Exception\DomainException;

final class ApiExceptionListener
{
    public function __construct(
        private LoggerInterface $logger
    ) {}

    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        dd("hello");
        // 🧾 LOG EVERYTHING
        $this->logger->error('API ERROR', [
            'message' => $exception->getMessage(),
            'class'   => get_class($exception),
            'trace'   => $exception->getTraceAsString(),
        ]);

        // 🎯 DOMAIN EXCEPTION HANDLING
        if ($exception instanceof DomainException) {

            $event->setResponse(new JsonResponse([
                'status'  => $exception->getStatusCode(),
                'error'   => $exception->getErrorCode(),
                'message' => $exception->getMessage(),
            ], $exception->getStatusCode()));

            return;
        }

        // 💥 GENERIC SAFE ERROR
        $event->setResponse(new JsonResponse([
            'status'  => 500,
            'error'   => 'INTERNAL_ERROR',
            'message' => 'An unexpected error occurred',
        ], 500));
    }
}