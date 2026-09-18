<?php

namespace Tests\DentalOffice\InvoiceBundle\Domain\Aggregate;

use DentalOffice\InvoiceBundle\Domain\Aggregate\Invoice;
use DentalOffice\InvoiceBundle\Domain\Aggregate\InvoiceItem;
use DentalOffice\InvoiceBundle\Domain\Aggregate\Refound;
use DentalOffice\InvoiceBundle\Domain\ValueObject\RefundAmount;
use PHPUnit\Framework\TestCase;

class RefoundTest extends TestCase
{
    public function test_create_found(): void
    {

         $invoiceItem = InvoiceItem::invoice(
            1,
            "Descreption Test",
            100.00,
            1,
            
         );

        $refundAmount = RefundAmount::fromPositiveRefundAmount(100.00);
        
        $refound = Refound::createRefound(
             1,
             $refundAmount,
             $invoiceItem ,
             "Reason Test"
         );


       
    }

}