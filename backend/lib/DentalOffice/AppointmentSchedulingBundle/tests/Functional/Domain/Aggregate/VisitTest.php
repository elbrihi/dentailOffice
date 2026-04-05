<?php

namespace DentalOffice\AppointmentSchedulingBundle\Tests\Functional\Domain\Aggregate;

use DentalOffice\AppointmentSchedulingBundle\Domain\Aggregate\Visit;
use DentalOffice\AppointmentSchedulingBundle\Domain\Exception\InvalidVisitAmountPaid;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\VisitAmountPaid;
use PHPUnit\Framework\TestCase;

class VisitTest extends TestCase
{

   public function test_amound_paid_is_negative()
   {


       try {
              Visit::createVisit(
              VisitAmountPaid::fromFloatPositive(-1)
           );
       } catch (InvalidVisitAmountPaid $e) {
        
         $negativeValueException = $e->negativeValue();
         $this->assertEquals($negativeValueException->getMessage(),"the amount paid value schould be positif" );
       }
   }

   public function test_amound_paid_is_not_negative()
   {
       $amoundPaid = Visit::createVisit(
          VisitAmountPaid::fromFloatPositive(1)
       );

    

       $this->assertEquals($amoundPaid->getVisitAmountPaid()->getAmountPaid(),1);

   }
    
}