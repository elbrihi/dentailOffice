<?php

namespace DentalOffice\AppointmentSchedulingBundle\Domain\Event;

final class VisitCreatedEvent
{

    private function __construct(
        private readonly int $medicalRecordID,
        private readonly array $payload,
        private readonly int $visitID
    )
    {
    }

    public static function fromVisitData(
        int $medicalRecordID,
        array $payload,
        int $visitID

    ):self
    {
        return new self(
           $medicalRecordID,
           $payload,
           $visitID

        );
    }
   
}