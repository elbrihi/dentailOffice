<?php


namespace DentalOffice\AppointmentSchedulingBundle\Tests\Functional\Domain\Exception;

use DentalOffice\AppointmentSchedulingBundle\Domain\Exception\InvalidPatientId;
use PHPUnit\Framework\TestCase;

class InvalidPatientIdTest extends TestCase
{

   protected function setUp(): void
   {
    parent::setUp();
   }

   public function test_it_creates_exception_from_value()
   {
       $exception = InvalidPatientId::fromValue('abc-123');


       $this->assertInstanceOf(InvalidPatientId::class,$exception);
       $this->assertSame('Invalid Patient ID provided: "abc-123".',$exception->getMessage());
      
   }

   public function test_it_creates_exception_empty()
   {
       $exception = InvalidPatientId::empty();
       
       $this->assertInstanceOf(InvalidPatientId::class ,$exception );

       $this->assertSame($exception->getMessage(),'Patient ID cannot be empty.');

   }

   public function test_it_creates_exception_invalid()
   {
       $exception = InvalidPatientId::invalid(123);

       $this->assertInstanceOf(InvalidPatientId::class ,$exception );

       $this->assertSame($exception->getMessage(),'Invalid PatientId "123". Must be a positive integer.');
   }
  
}