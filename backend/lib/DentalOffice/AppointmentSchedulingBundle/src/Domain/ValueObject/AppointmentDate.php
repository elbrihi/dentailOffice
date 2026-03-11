<?php

namespace DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject;

use DateTimeImmutable;
use DentalOffice\AppointmentSchedulingBundle\Domain\Exception\InvalidAppointmentDate;
use Symfony\Component\Validator\Constraints\Date;

final class AppointmentDate
{
    private DateTimeImmutable $date;
    
    private function __construct(DateTimeImmutable $date)
    {
      if($date >= new \DateTimeImmutable())
      {
        throw InvalidAppointmentDate::invalid($date);
      }

      $this->date = $date;
    }

    public static function fromDateFrom(DateTimeImmutable $date):self
    {
     
      return new self($date);
    }

    public function getAppointmentDate():DateTimeImmutable
    {
        return $this->date;
    }


}