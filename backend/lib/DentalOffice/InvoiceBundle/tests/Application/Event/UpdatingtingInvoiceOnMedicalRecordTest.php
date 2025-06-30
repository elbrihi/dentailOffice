<?php

namespace DentalOffice\InvoiceBundle\Tests\Application\Event;

use DentalOffice\InvoiceBundle\Application\Event\InvoiceCreatedEvent;
use DentalOffice\InvoiceBundle\Tests\InvoiceApiTestCase;

class UpdatingtingInvoiceOnMedicalRecordTest  extends InvoiceApiTestCase
{
       
    public function testUpdatingtingInvoiceOnMedicalRecord(): void
    {
        $medicalRecord = $this->saveMedicalRecordWithVisitsForUpdatingInvoice([
            ['amount_paid' => 300],
            ['amount_paid' => 400],
            ['amount_paid' => 300],
        ], agreedAmount: 1000);

        $this->dispatcher->dispatch(new InvoiceCreatedEvent($medicalRecord->getId()));

    }
}
