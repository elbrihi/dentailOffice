<?php

namespace DentalOffice\InvoiceBundle\Tests\Domain\ValueObject;

use DentalOffice\InvoiceBundle\Domain\ValueObject\InvoiceItemPaymentStatus;
use DentalOffice\PaymentsBundle\Domain\Exception\InvalidBilledException;
use DentalOffice\PaymentsBundle\Domain\Exception\InvalidPaidException;
use DentalOffice\PaymentsBundle\Domain\Exception\InvalidRefundedException;
use PHPUnit\Framework\TestCase;

class InvoiceItemPaymentStatusTest extends TestCase
{
    
    
    public function test_should_create_an_unbilled_status(): void
    {
        $paymentStatus = InvoiceItemPaymentStatus::unbilled();
        $this->assertEquals('unbilled', $paymentStatus->value());
        $this->assertTrue($paymentStatus->isUnbilled());
        $this->assertFalse($paymentStatus->isPaid());
        $this->assertFalse($paymentStatus->isBilled());
        $this->assertFalse($paymentStatus->isPartiallyPaid());
        $this->assertFalse($paymentStatus->isRefunded());
        
    }

    public function test_it_should_create_a_billed(): void
    {
        $status = InvoiceItemPaymentStatus::billed(InvoiceItemPaymentStatus::UNBILLED);
        
        
        $this->assertEquals('billed', $status->value());
        $this->assertTrue($status->isBilled());
        $this->assertFalse($status->isPaid());
        $this->assertEquals('billed', (string) $status);
    }

    public function test_it_should_throw_an_invalid_transition_exception_when_billed(): void
    {
        try {
            $status = InvoiceItemPaymentStatus::billed(InvoiceItemPaymentStatus::BILLED);
        } catch (InvalidBilledException $e) {            
            $this->assertEquals(InvalidBilledException::invalidTransition(InvoiceItemPaymentStatus::BILLED, InvoiceItemPaymentStatus::BILLED)->getMessage(), $e->getMessage());
        
        }
    }

    public function test_it_should_create_partially_paid_status()
    {
        $paymentStatus = InvoiceItemPaymentStatus::partiallyPaid(
                      InvoiceItemPaymentStatus::BILLED);
        $this->assertEquals('partially_paid', $paymentStatus->value());
        $this->assertTrue($paymentStatus->isPartiallyPaid());
        $this->assertFalse($paymentStatus->isPaid());
        $this->assertFalse($paymentStatus->isBilled());
        $this->assertFalse($paymentStatus->isUnbilled());
        $this->assertFalse($paymentStatus->isRefunded());
        $this->assertEquals('partially_paid', (string) $paymentStatus);
    }

    public function test_it_should_create_a_paid_status(): void
    {
     
        $status = InvoiceItemPaymentStatus::paid(InvoiceItemPaymentStatus::BILLED);
        
        $this->assertEquals('paid', $status->value());
        $this->assertTrue($status->isPaid());

        $status = InvoiceItemPaymentStatus::paid(InvoiceItemPaymentStatus::PARTIALLY_PAID);
        $this->assertEquals('paid', (string) $status);

    }

    public function test_it_should_throw_an_invalid_transition_exception_when_paid(): void
    {
        try {
            $status = InvoiceItemPaymentStatus::paid(InvoiceItemPaymentStatus::UNPAID);
        } catch (InvalidPaidException $e) {
            $this->assertEquals(InvalidPaidException::invalidTransition(InvoiceItemPaymentStatus::UNPAID, InvoiceItemPaymentStatus::PAID)->getMessage(), $e->getMessage());
        }
    }
    public function test_it_should_create_refunded_status()
    {
        $status = InvoiceItemPaymentStatus::refunded(InvoiceItemPaymentStatus::PAID);
        $this->assertEquals('refunded', $status->value());
        $this->assertEquals('refunded', (string) $status);

        $status = InvoiceItemPaymentStatus::refunded(InvoiceItemPaymentStatus::PARTIALLY_PAID);
        $this->assertEquals('refunded', $status->value());
        $this->assertEquals('refunded', (string) $status);
    }

    public function test_it_should_throw_an_invalid_transition_exception_when_refunded(): void
    {
        try {
            $status = InvoiceItemPaymentStatus::refunded(InvoiceItemPaymentStatus::UNPAID);
        } catch (InvalidRefundedException $e) {
            $this->assertEquals(InvalidRefundedException::invalidTransition(InvoiceItemPaymentStatus::UNPAID, InvoiceItemPaymentStatus::REFUNDED)->getMessage(), $e->getMessage());
        }
    }

   
}