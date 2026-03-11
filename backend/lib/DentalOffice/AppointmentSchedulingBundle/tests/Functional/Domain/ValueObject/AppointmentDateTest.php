<?php

namespace DentalOffice\AppointmentSchedulingBundle\Tests\Functional\Domain\ValueObject;

use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\AppointmentDate;
use PHPUnit\Framework\TestCase;

class AppointmentDateTest extends TestCase
{
   protected function setUp(): void
   {
    parent::setUp();
   }

   public function test_it_creates_appointment_date()
   {
      $date = new \DateTimeImmutable('2000-01-01 10:00:00');

      $fromDateForm = AppointmentDate::fromDateFrom($date);

      $this->assertInstanceOf(AppointmentDate::class,$fromDateForm);

      $this->assertSame($date,$fromDateForm->getAppointmentDate());
      
   }

}