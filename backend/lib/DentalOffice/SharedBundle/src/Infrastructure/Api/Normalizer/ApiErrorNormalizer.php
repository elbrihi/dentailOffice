<?php


namespace DentalOffice\SharedBundle\Infrastructure\Api\Normalizer;

use DentalOffice\AppointmentSchedulingBundle\Domain\Exception\PatientNotFoundException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Throwable;

class ApiErrorNormalizer
{
    // public function supportsNormalization($data, string $format = null): bool
    // {
    //     return $data instanceof Throwable;
    // }

    // public function normalize($exception, string $format = null, array $context = []): array
    // {
        
    //     dd("hello");
    //     return [
    //         'status' => $context['status_code'] ?? 500,
    //         'message' => $exception->getMessage(),
    //         'error' => $this->mapErrorCode($exception),
    //     ];
    // }

    // private function mapErrorCode(Throwable $exception): string
    // {
    //     return match (true) {
    //         $exception instanceof PatientNotFoundException
    //             => 'PATIENT_NOT_FOUND',

    //         default => 'INTERNAL_ERROR',
    //     };
    // }
}