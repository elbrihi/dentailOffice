<?php


namespace DentalOffice\InvoiceBundle\Application\Event;


class InvoiceUpdatedEvent
{
    public function __construct(
        private int $medicalRecordId
    )
    {
    } 
    
    public function getMedicalRecordId()
    {
        return $this->medicalRecordId;
    }
}