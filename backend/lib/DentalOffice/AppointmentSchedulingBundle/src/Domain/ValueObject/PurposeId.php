<?php

namespace DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject;

use DentalOffice\AppointmentSchedulingBundle\Domain\Exception\InvalidPurpose;

final class PurposeId
{

    private $value; 

    private const MIN_LENGTH=3;
    private const MAX_LENGTH=255;

    private function __construct(string $value)
    {
        $value = trim($value);    

        if($value === '')
        {
            throw InvalidPurpose::empty();
        }

        if(mb_strlen($value) >=  self::MAX_LENGTH )
        {
          throw InvalidPurpose::tooLength($value);
        }

        if(mb_strlen($value) <= self::MIN_LENGTH)
        {
        
          throw InvalidPurpose::tooShort($value);
        }

        $this->value = $value;

    }
    public static function fromString(string $value):self
    {
      return new self($value);
    }

    public static function  equals(self $other):bool
    {
       return $this->value === $other->value;
    }

    public function getPruposeValue():string
    {
       return $this->value;
    }
}