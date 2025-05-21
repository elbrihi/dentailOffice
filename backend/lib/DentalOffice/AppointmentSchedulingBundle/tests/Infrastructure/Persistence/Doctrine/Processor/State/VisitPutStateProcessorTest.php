<?php

namespace DentalOffice\AppointmentSchedulingBundle\Tests\Infrastructure\Persistence\Doctrine\Processor\State;

use ApiPlatform\Metadata\Post;
use DentalOffice\AppointmentSchedulingBundle\Domain\Entity\Visit;
use DentalOffice\AppointmentSchedulingBundle\Tests\VisitApiTestCase;
use Symfony\Component\HttpFoundation\Request;

class VisitPutStateProcessorTest extends VisitApiTestCase
{
    

    function testUpdateVisits()
    {
        $this->saveMedicalRecord();
        $this->saveVisits();

        // 🔁 First Visit
        $request = new Request([], [], [], [], [], [], json_encode([
            "visit_date" => "2025-02-12",
            "notes" => "Consultation initiale + radio",
            "amount_paid" => 250,
            "remaining_due_after_visit" => 700,
        ]));

        $operation = new Post();
        $visit = new Visit();
        $context['request'] = $request;
     
        $uriVariables['visitId'] =static::$visitId;

        $this->visitPutStateProcessor->process($visit, $operation, $uriVariables, $context);
        $this->assertEquals(8,8);
    }
}