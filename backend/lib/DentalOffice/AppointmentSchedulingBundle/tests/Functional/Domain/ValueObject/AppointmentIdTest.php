<?php

namespace DentalOffice\PatientBundle\Tests\Functional\Domain\ValueObject;

use DentalOffice\AppointmentSchedulingBundle\Domain\Aggregate\Appointment;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\AppointmentId;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\TimeSlot;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertInstanceOf;

class AppointmentIdTest extends TestCase
{
   public function setUp():void
   {
      parent::setUp();
   }

   public function test_it_is_value_generated()
   {
       
      $timeSlot = new TimeSlot(
         new \DateTimeImmutable(),
         new \DateTimeImmutable()
      );
      
      $appointmentId = AppointmentId::fromInt(5) ;

      assertInstanceOf(AppointmentId::class,$appointmentId);
      $this->assertSame($appointmentId->generate(),5);

      
   }


}