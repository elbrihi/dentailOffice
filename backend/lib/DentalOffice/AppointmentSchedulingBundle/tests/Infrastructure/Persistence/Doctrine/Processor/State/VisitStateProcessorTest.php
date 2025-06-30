<?php


namespace DentalOffice\AppointmentSchedulingBundle\Tests\Infrastructure\Persistence\Doctrine\Processor\State;

use ApiPlatform\Metadata\Post;
use DateTimeImmutable;
use DentalOffice\AppointmentSchedulingBundle\Application\Dto\VisitInputDto;
use DentalOffice\AppointmentSchedulingBundle\Domain\Entity\Visit;
use DentalOffice\AppointmentSchedulingBundle\Tests\VisitApiTestCase;
use DentalOffice\InvoiceBundle\Domain\Entity\Invoice;
use DentalOffice\MedicalRecordBundle\Domain\Entity\MedicalRecord;
use DentalOffice\PaymentsBundle\Domain\Entity\Payment;
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
            "remaining_due_after_visit" => 1200,
            "duration_minutes" => 40,
            "type" => "consultation",
            "payments" => [
                [
                    "method" => "Virement",
                    "payment_date" => "2025-03-01"
                ]
            ],
        ]));

        $operation = new Post();
        $visitDto1 = new VisitInputDto();
        $context1['request'] = $request1;
        $uriVariables['medicalRecordId'] = static::$medicalRecordId;

        $this->visitPostStateProcessor->process($visitDto1, $operation, $uriVariables, $context1);

        // 🔁 Second Visit
        $request2 = new Request([], [], [], [], [], [], json_encode([
            "visit_date" => "2025-03-15",
            "notes" => "Suivi traitement",
            "amount_paid" => 400,
            "remaining_due_after_visit" => 800,
            "duration_minutes" => 40,
            "type" => "consultation",
            "payments" => [
                [
                    "method" => "Carte",
                    "payment_date" => "2025-03-15"
                ]
            ],
        ]));

        $visitDto2 = new Visit();
        $context2['request'] = $request2;

        $this->visitPostStateProcessor->process($visitDto2, $operation, $uriVariables, $context2);

        $this->entityManager->clear(); // Clear EM to fetch fresh

        /** @var MedicalRecord $medicalRecord */
        $medicalRecord = $this->entityManager->getRepository(MedicalRecord::class)
            ->find(static::$medicalRecordId);

        /** @var Invoice $invoice */
        $invoice = $this->entityManager->getRepository(Invoice::class)->findOneBy([]);
        $this->assertCount(2, $this->entityManager->getRepository(Visit::class)->findAll());
        $this->assertCount(2, $this->entityManager->getRepository(Payment::class)->findAll());
        $this->assertCount(1, $this->entityManager->getRepository(Invoice::class)->findAll());

        $invoice = $this->entityManager->getRepository(Invoice::class)->findOneBy([]);
        $this->assertEquals(2, $invoice->getPayments()->count());

            // MedicalRecord: agreed_amount should still be 1500
        $this->assertEquals(1000, $medicalRecord->getAgreedAmout());

        // MedicalRecord: total paid should be 700 (300 + 400)
        $this->assertEquals(700, $medicalRecord->getTotalPaid());

        // MedicalRecord: remaining due should be 800
        $this->assertEquals(300, $medicalRecord->getRemainingDue());

        // Invoice: total amount must match agreed amount
        $this->assertEquals(1000, $invoice->getTotalAmount());

        // Invoice: total paid must match total paid on record
        $this->assertEquals(700, $invoice->getTotalPaid());

        // Invoice: remaining due must match record remaining due
        $this->assertEquals(300, $invoice->getRemainingDue());
 
    }

  
}