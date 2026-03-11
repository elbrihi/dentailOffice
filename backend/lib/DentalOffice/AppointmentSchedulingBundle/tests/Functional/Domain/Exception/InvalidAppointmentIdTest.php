<?php 


namespace DentalOffice\AppointmentSchedulingBundle\Tests\Functional\Domain\Exception;

use DentalOffice\AppointmentSchedulingBundle\Domain\Exception\InvalidAppointmentId;
use PHPUnit\Framework\TestCase;

class InvalidAppointmentIdTest extends TestCase
{
    protected function setUp(): void
    {
       parent::setUp();
    }

    public function test_it_creates_invalid_value_appointment()
    {
        $invalidAppoitment = InvalidAppointmentId::invalid(-4);

        $this->assertInstanceOf(InvalidAppointmentId::class,$invalidAppoitment);
        $this->assertSame($invalidAppoitment->getMessage(),'Invalid AppointmentId -4. It must be a positive integer.');
    }
}