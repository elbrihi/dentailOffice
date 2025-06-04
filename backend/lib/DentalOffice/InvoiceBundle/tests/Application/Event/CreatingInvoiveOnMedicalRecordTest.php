<?php

namespace DentalOffice\InvoiceBundle\Tests\Application\Event;

use DateTimeImmutable;
use DentalOffice\InvoiceBundle\Application\Event\InvoiceCreatedEvent;
use DentalOffice\InvoiceBundle\Domain\Entity\Invoice;
use DentalOffice\InvoiceBundle\Tests\InvoiceApiTestCase;
use DentalOffice\MedicalRecordBundle\Domain\Entity\MedicalRecord;

class CreatingInvoiveOnMedicalRecordTest extends InvoiceApiTestCase
{
    public function testCreatingInvoiceOnMedicalRecord()
    {
                // Arrange: Load fixtures or manually create test MedicalRecord with visits
        $medicalRecord = $this->createMedicalRecordWithVisits([
            ['amount_paid' => 300],
            ['amount_paid' => 400],
            ['amount_paid' => 300],
        ], agreedAmount: 1000);

        $medicalRecordId = $medicalRecord->getId();
        $this->dispatcher->dispatch(new InvoiceCreatedEvent($medicalRecordId));
        
        $invoice= $this->entityManager->getRepository(Invoice::class)->findOneBy(
            [
                'medicalRecord' => $medicalRecord  
            ]
        );
        $this->assertEquals($invoice->getTotalAmount(),1000);
        $this->assertEquals($invoice->getTotalPaid(),0);
        $this->assertEquals($invoice->getRemainingDue(),1000);

    }


}