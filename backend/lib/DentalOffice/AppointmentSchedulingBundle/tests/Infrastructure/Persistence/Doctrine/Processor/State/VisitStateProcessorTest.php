<?php


namespace DentalOffice\AppointmentSchedulingBundle\Tests\Infrastructure\Persistence\Doctrine\Processor\State;

use ApiPlatform\Metadata\Post;
use DateTimeImmutable;
use DentalOffice\AppointmentSchedulingBundle\Domain\Entity\Visit;
use DentalOffice\AppointmentSchedulingBundle\Tests\VisitApiTestCase;


use Symfony\Component\HttpFoundation\Request;

class VisitStateProcessorTest extends VisitApiTestCase
{




    public function testVisitProcessorPersist()
    {
        $this->saveMedicalRecord();

    // 🔁 First Visit
    $request1 = new Request([], [], [], [], [], [], json_encode([
        "visit_date" => "2025-02-12",
        "notes" => "Consultation initiale + radio",
        "amount_paid" => 300,
        "remaining_due_after_visit" => 700,
    ]));

    $operation = new Post();
    $visit1 = new Visit();
    $context1['request'] = $request1;
    $uriVariables['medicalRecordId'] = static::$medicalRecordId;

    $this->visitPostStateProcessor->process($visit1, $operation, $uriVariables, $context1);

    // 🔁 Second Visit
    $request2 = new Request([], [], [], [], [], [], json_encode([
        "visit_date" => "2025-03-15",
        "notes" => "Suivi traitement",
        "amount_paid" => 400,
        "remaining_due_after_visit" => 500,
    ]));

    $visit2 = new Visit();
    $context2['request'] = $request2;

    $this->visitPostStateProcessor->process($visit2, $operation, $uriVariables, $context2);

        // 🔁 Second Visit
    $request3 = new Request([], [], [], [], [], [], json_encode([
        "visit_date" => "2025-03-15",
        "notes" => "Suivi traitement",
        "amount_paid" => 300,
        "remaining_due_after_visit" => 500,
    ]));

    $visit3 = new Visit();
    $context3['request'] = $request3 ;

    $this->visitPostStateProcessor->process($visit3, $operation, $uriVariables, $context3);


    // ✅ Assert that two visits were created
    $visits = $this->entityManager->getRepository(Visit::class)->findAll();
    $this->assertCount(3, $visits, "Expected exactly two Visits to be persisted.");

    // Optional: check both visits' values
    $visitDates = array_map(fn($v) => $v->getVisitDate()->format('Y-m-d'), $visits);
    $this->assertContains("2025-02-12", $visitDates);
    $this->assertContains("2025-03-15", $visitDates);

    foreach ($visits as $v) {
        $this->assertEquals(static::$medicalRecordId, $v->getMedicalRecord()->getId());
    }
    }

  
}