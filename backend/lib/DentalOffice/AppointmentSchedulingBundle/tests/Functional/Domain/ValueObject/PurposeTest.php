<?php


namespace App\Tests\Functional\Domain\ValueObject;

use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\PurposeId;
use PHPUnit\Framework\TestCase;

class PurposeTest extends TestCase
{
   protected function setUp(): void
   {
     parent::setUp();
   }


   public function test_it_creates_purpose()
   {
        $purpose =  PurposeId::fromString("test test test ");

        $this->assertInstanceOf(PurposeId::class,  $purpose);
        $this->assertSame($purpose->getPruposeValue(),"test test test");
   }

}