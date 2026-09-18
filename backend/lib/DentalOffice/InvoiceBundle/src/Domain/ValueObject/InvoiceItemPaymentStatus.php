<?php

namespace DentalOffice\InvoiceBundle\Domain\ValueObject;

use DentalOffice\InvoiceBundle\Domain\Exception\InvalidPartiallyRefoubdedException;
use DentalOffice\InvoiceBundle\Domain\Exception\InvalidRefundAmountException;
use DentalOffice\PaymentsBundle\Domain\Exception\InvalidBilledException;
use DentalOffice\PaymentsBundle\Domain\Exception\InvalidPaidException;
use DentalOffice\PaymentsBundle\Domain\Exception\InvalidPartiallyPaidException;
use DentalOffice\PaymentsBundle\Domain\Exception\InvalidRefundedException;

final class InvoiceItemPaymentStatus 
{


    private string $invoiceItemPaymentStatus;

    public const UNBILLED = 'unbilled';
    public const BILLED = 'billed';
    public const PAID = 'paid';
    public const UNPAID = 'unpaid';
    public const PARTIALLY_PAID = 'partially_paid';
    public const PARTIALLY_REFUNDED = 'partially_refunded';
    public const REFUNDED = 'refunded';
    public const OVERPAID = 'overpaid';
    public const DONE = 'done';

    private function __construct(string $invoiceItemPaymentStatus)
    {
        $this->invoiceItemPaymentStatus = $invoiceItemPaymentStatus;
    }

    //handle invoice payment status 
    public static function handleInvoiceItemPaymentStatus
    (
        float $remainingDue, 
        float $totalAmount, 
        float $agreedAmount
    ): self
    {
        
        if ($remainingDue === 0) {
            return self::paid(self::BILLED);
        }

        if ($remainingDue < $totalAmount) {
           // return self::partiallyPaid();
        }

        return self::unbilled();
    }
    public static function unbilled(string $invoiceItemPaymentStatus = self::UNBILLED): self
    {
        return new self($invoiceItemPaymentStatus);
    }


    public static function billed(string $invoiceItemPaymentStatus): self
    {

        if (!in_array($invoiceItemPaymentStatus, [self::UNBILLED])) {

            return throw InvalidBilledException::invalidTransition($invoiceItemPaymentStatus, self::BILLED);
        }

        return new self(self::BILLED);
    }

    public static function paid(string $status): self
    {
        if (!in_array($status , [self::BILLED,self::PARTIALLY_PAID]) ) {
            return throw InvalidPaidException::invalidTransition($status, self::PAID);
        }
        return new self(self::PAID);
    }


    public static function partiallyPaid(string $status): self
    {

        if (!in_array($status , [self::BILLED]) ) {
            return throw InvalidPartiallyPaidException::invalidTransition($status, self::PARTIALLY_PAID);
        }
        return new self(self::PARTIALLY_PAID);
    }

    public static function partiallyRefounded(string $status)
    {
        if(!in_array($status,[self::PAID,self::PARTIALLY_PAID]))
        {
           
            return throw InvalidPartiallyRefoubdedException::invalidPartiallyRefouned($status);
        }
        return new self(self::PARTIALLY_REFUNDED);

    }

    public static function refunded(string $status): self
    {
       
        
        if (!in_array($status , [self::PAID,self::PARTIALLY_PAID]) ) {
            
            return throw InvalidRefundedException::invalidTransition($status, self::REFUNDED);
        }
      
        return new self(self::REFUNDED);
    }

    public function value(): string
    {
        return $this->invoiceItemPaymentStatus;
    }

    public function isUnbilled(): bool
    {
        return $this->invoiceItemPaymentStatus === self::UNBILLED;
    }

    public function isBilled(): bool
    {
        return $this->invoiceItemPaymentStatus === self::BILLED;
    }

    public function isPaid(): bool
    {
        return $this->invoiceItemPaymentStatus === self::PAID;
    }

    public function isPartiallyPaid(): bool
    {
        return $this->invoiceItemPaymentStatus === self::PARTIALLY_PAID;
    }

    public function isRefunded(): bool
    {
        return $this->invoiceItemPaymentStatus === self::REFUNDED;
    }

    public function __toString(): string
    {
        return $this->invoiceItemPaymentStatus;
    }

    public static function unpaid(): self
    {
        return new self(self::UNPAID);
    }

    public static function overpaid(string $status): self
    {
        
        return new self(self::OVERPAID);
    }

    public static function done(): self
    {
        return new self(self::DONE);
    }

    public function getInvoiceItemPaymentStatus():string
    {
        return $this->invoiceItemPaymentStatus;
    }

    
    


}