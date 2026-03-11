<?php

namespace DentalOffice\AppointmentSchedulingBundle\Tests\Functional\Domain\ValueObject;

use DateTimeImmutable;
use DentalOffice\AppointmentSchedulingBundle\Domain\Aggregate\Appointment;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\AppointmentDate;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\PatientId;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\PractitionerId;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\PurposeId;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\TimeSlot;
use PHPUnit\Framework\TestCase;

class AppointmentTest extends TestCase
{
   private \DateTimeImmutable $start ; 
   private \DateTimeImmutable $end;
   protected function setUp():void
   {
        $this->start = new \DateTimeImmutable('2026-03-01 09:00:00');
        $this->end = new \DateTimeImmutable('2026-03-01 10:00:00');
   }

   public function test_it_creates_valid_appointment()
   {

        $patientId = PatientId::fromInt("12");
        
        $timeSlot = new TimeSlot($this->start, $this->end); 

        $parctionerId = PractitionerId::fromInt(6);

        $purposeId = PurposeId::fromString("the first appointment");
      
        $appointment = Appointment::book(
            $patientId,
            $timeSlot,
            $parctionerId,
            $purposeId
        );
        
        $this->assertInstanceOf(Appointment::class,$appointment);
   
    } 
}