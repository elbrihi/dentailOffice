<?php

namespace DentalOffice\AppointmentSchedulingBundle\Tests\Infrastructure\Persistence\Doctrine\Processor\State;

use ApiPlatform\Metadata\Post;
use DentalOffice\AppointmentSchedulingBundle\Domain\Entity\Visit;
use DentalOffice\AppointmentSchedulingBundle\Tests\VisitApiTestCase;
use DentalOffice\InvoiceBundle\Domain\Entity\Invoice;
use DentalOffice\MedicalRecordBundle\Domain\Entity\MedicalRecord;
use DentalOffice\PaymentsBundle\Domain\Entity\Payment;
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


    // ✅ Refresh state
    $this->entityManager->clear();

    /** @var MedicalRecord $medicalRecord */
    $medicalRecord = $this->entityManager->getRepository(MedicalRecord::class)
        ->find(static::$medicalRecordId);

    /** @var Invoice $invoice */
    $invoice = $this->entityManager->getRepository(Invoice::class)->findOneBy([]);

    // ✅ Visits & Payments & Invoice
    $this->assertCount(3, $this->entityManager->getRepository(Visit::class)->findAll());
    $this->assertCount(3, $this->entityManager->getRepository(Payment::class)->findAll());
    $this->assertCount(1, $this->entityManager->getRepository(Invoice::class)->findAll());
    $this->assertEquals(3, $invoice->getPayments()->count());

    // ✅ Agreed amount must be 1000
    $this->assertEquals(1000, $medicalRecord->getAgreedAmout());

    // ✅ Total paid is 300 + 400 = 700
    $this->assertEquals(250, $medicalRecord->getTotalPaid());

    // ✅ Remaining due is 1000 - 700 = 300
    $this->assertEquals(750, $medicalRecord->getRemainingDue());

    // ✅ Invoice must match MedicalRecord
    $this->assertEquals(1000, $invoice->getTotalAmount());
    $this->assertEquals(250, $invoice->getTotalPaid());
    $this->assertEquals(750, $invoice->getRemainingDue());
        
        

    }
}