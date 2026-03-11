<?php

namespace DentalOffice\AppointmentSchedulingBundle\Tests\Functional\Domain\Exception;

use DentalOffice\AppointmentSchedulingBundle\Domain\Exception\InvalidAppointmentDate;
use PHPUnit\Framework\TestCase;

class InviladAppointmentDateTest extends TestCase
{
   protected function setUp(): void
   {
    parent::setUp();
   }

  public function test_it_creates_inivlid_date_value()
  {
      
      $data = new \DateTimeImmutable();

      $invalidDate = InvalidAppointmentDate::invalid($data);

      $this->assertInstanceOf(InvalidAppointmentDate::class, $invalidDate);
     
      $this->assertSame($invalidDate->getMessage(),
      
      sprintf('the invalid date "%s" should not in past',
            $data->format("T-m-d H:i:s")
      ));

  
  }
}