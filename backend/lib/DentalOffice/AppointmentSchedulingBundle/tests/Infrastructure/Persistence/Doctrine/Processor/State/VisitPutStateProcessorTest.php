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
            "visit_date" => "2025-03-17",
            "notes" => "Consultation initiale + radio test",
            "amount_paid" => 250,
            "remaining_due_after_visit" => 700,
            "duration_minutes" => 42,
            "type"=> "consultation",
            "payments" => [
                [
                    "method" => "Carte test",
                    "payment_date" => "2025-03-17"
                ]
            ],
        ]));

        $operation = new Post();
        $visit = new Visit();
        $context['request'] = $request;
     
        $visitId = $uriVariables['id'] =static::$visitId;
 
        $this->visitPutStateProcessor->process($visit, $operation, $uriVariables, $context);
        
        

    }
}