<?php


namespace DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject;

use DentalOffice\AppointmentSchedulingBundle\Domain\Exception\InvalidVisitAmountPaid;

class VisitAmountPaid
{
    private function __construct(private float $amountPaid)
    {
     
    }


    public static function fromFloatPositive(float $amountPaid):self
    {
      
        if($amountPaid < 0)
        {
            throw InvalidVisitAmountPaid::negativeValue();
        }

        return new self($amountPaid);
    }


    /**
     * Get the value of amountPaid
     */ 
    public function getAmountPaid():float
    {
        return $this->amountPaid;
    }
 
}