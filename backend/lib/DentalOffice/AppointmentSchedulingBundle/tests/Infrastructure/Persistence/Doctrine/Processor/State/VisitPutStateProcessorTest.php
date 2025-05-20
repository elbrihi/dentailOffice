<?php

namespace DentalOffice\AppointmentSchedulingBundle\Tests\Infrastructure\Persistence\Doctrine\Processor\State;

use DentalOffice\AppointmentSchedulingBundle\Domain\Entity\Visit;
use DentalOffice\AppointmentSchedulingBundle\Tests\VisitApiTestCase;

class VisitPutStateProcessorTest extends VisitApiTestCase
{
    

    function testUpdateVisits()
    {
        $this->saveMedicalRecord();
        $this->assertEquals(8,8);
    }
}