<?php


namespace DentalOffice\MedicalRecordBundle\Domain\ValueObject;

class MedicalRecordId
{
   
    private $value =1;
    private function __construct(
       int $value
    )
    {
        $this->value=$value;
    }

    public static function toInt($value):self
    {
       
        if(strlen($value)<1)
        {
            throw MedicalRecordChiefComplaint::chiefComplaint($value);
        }
        return new self($value);
    }

    public function getMedicalRecordID():int
    {
        return $this->value;
    }
}