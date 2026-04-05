<?php

namespace DentalOffice\AppointmentSchedulingBundle\Domain\Event;

// MedicalRecordCreatedSubscriber

class AppointmentCompleted
    {

        private function __construct(
        private readonly int $medicalRecordId =0,
        private readonly int $appointmentId,
        private readonly int $patientId,
        private readonly int $dentistId,
        private readonly \DateTimeImmutable $completedAt,
        private readonly array $uriVariables,
        private readonly array $payload
    
    ) {}

    /**
     * Named constructor (clear intent)
     */
    public static function fromData(
         int $medicalRecordId ,
        int $appointmentId,
        int $patientId,
        int $dentistId,
        ?\DateTimeImmutable $completedAt = null,
        $uriVariables,
         array $payload
    ): self {
        return new self(
            $medicalRecordId ,
            $appointmentId,
            $patientId,
            $dentistId,
            $completedAt ?? new \DateTimeImmutable(),
            $uriVariables,
            $payload
        );
    }

    // -------------------
    // Getters (read-only)
    // -------------------

    public function getAppointmentId(): int
    {
        return $this->appointmentId;
    }

    public function getPatientId(): int
    {
        return $this->patientId;
    }

    public function getDentistId(): int
    {
        return $this->dentistId;
    }

    public function getCompletedAt(): \DateTimeImmutable
    {
        return $this->completedAt;
    }


    /**
     * Get the value of payload
     */ 
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * Set the value of payload
     *
     * @return  self
     */ 
    public function setPayload($payload)
    {
        $this->payload = $payload;

        return $this;
    }

    /**
     * Get the value of uriVariables
     */ 
    public function getUriVariables()
    {
            return $this->uriVariables;
    }

    /**
     * Set the value of uriVariables
     *
     * @return  self
     */ 
    public function setUriVariables($uriVariables)
    {
            $this->uriVariables = $uriVariables;

            return $this;
    }


}