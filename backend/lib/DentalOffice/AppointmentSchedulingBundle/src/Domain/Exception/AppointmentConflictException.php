<?php

namespace DentalOffice\AppointmentSchedulingBundle\Domain\Exception;

use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\PractitionerId;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\TimeSlot;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class AppointmentConflictException  extends HttpException
{
       private function __construct(
        PractitionerId $practitionerId,
        TimeSlot $timeSlot,
        string $message = '',
        int $code = 0
    ) {
        $detail = sprintf(
            'Practitioner "%s" already has an appointment between %s and %s.',
            $practitionerId->generate(),
            $timeSlot->getStart()->format('Y-m-d H:i'),
            $timeSlot->getEnd()->format('Y-m-d H:i')
        );

        $headers = [
            'Content-Type' => 'application/problem+json',
        ];

        $problem = [
            'type' => 'https://example.com/probs/appointment-conflict',
            'title' => 'Conflict',
            'status' => 409,
            'detail' => $detail,
        ];

        parent::__construct(409, json_encode($problem, JSON_THROW_ON_ERROR), null, $headers);
    }

    public static function forTimeSlot(PractitionerId $practitionerId, TimeSlot $timeSlot): self
    {
        return new self($practitionerId, $timeSlot);
    }

} 