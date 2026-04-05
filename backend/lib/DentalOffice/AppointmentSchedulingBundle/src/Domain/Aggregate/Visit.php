<?php

namespace DentalOffice\AppointmentSchedulingBundle\Domain\Aggregate;

use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\VisitAmountPaid;

final class Visit
{
    private function __construct(private VisitAmountPaid $visitAmountPaid)
    {
     
    }

    public static function createVisit(
     VisitAmountPaid $visitAmountPaid
    ):self

    {
        return new self($visitAmountPaid);         
    }

    public function getVisitAmountPaid():VisitAmountPaid
    {
        return $this->visitAmountPaid;
    }
}