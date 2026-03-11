<?php 


namespace DentalOffice\AppointmentSchedulingBundle\Tests\Functional\Domain\ValueObject;

use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\PatientId;
use PHPUnit\Framework\TestCase;



class PatientIdTest extends TestCase
{
   private static int $patientId=6  ;  
   protected function setUp():void
   {
     parent::setUp();
   }

  

   public function test_has_int_value()
   {
      
        $patientId = PatientId::fromInt(10);
       
        $this->assertInstanceOf(PatientId::class,$patientId );
       
        $this->assertIsInt($patientId ->toInt());
        
   }

   public function test_checking_the_both_value_are_same()
   {
        $id1 = PatientId::fromInt(10);
        $id2 = PatientId::fromInt(10);
        $id3 = PatientId::fromInt(11);

        $this->assertIsBool($id1->equals($id1), true);
        $this->assertIsBool($id2->equals($id2), true);
        $this->assertIsBool($id3->equals($id3), false);
        

   }
}