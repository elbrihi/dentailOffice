<?php 

namespace DentalOffice\AppointmentSchedulingBundle\Tests\Functional\Domain\ValueObject;

use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\PractitionerId;
use PHPUnit\Framework\TestCase;

class PractitionerIdTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_it_creates_id_practioner_id()
    {

        $practionnerId = PractitionerId::fromInt(6);

        $this->assertInstanceOf(PractitionerId::class,$practionnerId);
        $this->assertSame($practionnerId->PractitionerId(),6);

  

    }
}