<?php

namespace DentalOffice\InvoiceBundle\Tests\Infrastructure\Persistence\Doctrine\Provider\State;

use ApiPlatform\Metadata\GetCollection;
use DentalOffice\InvoiceBundle\Tests\InvoiceApiTestCase;

class InvoicesGetCollectionProviderTest extends InvoiceApiTestCase
{
    function testInvoicesGetCollectionProvier()
    {
        

        $this->saveMedicalRecords(); 

        $operation = new GetCollection(); 

        $context["filters"]["page"] = 1;
        $context["filters"]["itemsPerPage"] = 1;
        
        $context["filters"]["after_invoice_date"] = '2025-03-12';
       $context["filters"]["befor_invoice_date"] = '2025-11-12';
        $paginator = $this->invoicesGetCollection->provide($operation,[],$context);
        
        $result = iterator_to_array($paginator);

      //  dd($result);
        // Assert the paginator returns no more than 5 items
        $this->assertLessThanOrEqual(5, count($result));

        $afterDate = new \DateTimeImmutable($context["filters"]["after_invoice_date"]);
        $beforeDate = (new \DateTimeImmutable($context["filters"]["befor_invoice_date"]))->setTime(23, 59, 59);

        foreach ($result as $invoice) {
            $invoiceDate = $invoice->getInvoiceDate();

            // Assert invoice date is within the range
            $this->assertGreaterThanOrEqual($afterDate, $invoiceDate, "Invoice date is before after_invoice_date");
            $this->assertLessThanOrEqual($beforeDate, $invoiceDate, "Invoice date is after befor_invoice_date");
        }
        
        
    }
}