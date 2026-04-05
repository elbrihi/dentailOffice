<?php


namespace DentalOffice\AppointmentSchedulingBundle\Domain\Exception;

final class PatientNotFoundException extends \DomainException
{
    public function __construct(int $patientId)
    {
        parent::__construct(
            sprintf('Patient with ID %d not found.', $patientId)
        );
    }
}