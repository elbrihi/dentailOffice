<?php

namespace DentalOffice\PaymentsBundle\Domain\ValueObject;

use DentalOffice\PaymentsBundle\Domain\Exception\InvalidPaidException;
use DentalOffice\PaymentsBundle\Domain\Exception\InvalidPartiallyRefundedException;
use DentalOffice\PaymentsBundle\Domain\Exception\InvalidPendingException;
use DentalOffice\PaymentsBundle\Domain\Exception\InvalidRefundedException;

class PaymentStatus
{

   private const UNBILLED = 'unbilled';

   public const BILLED = 'billed';

   public const PAID = 'paid';

   public const UNPAID = 'unpaid';

   // 🟡 Payment created but not processed yet
   private const PENDING = 'pending';

   // 🔵 Payment currently processing
   // bank/API/terminal verification running
   private const PROCESSING = 'processing';

   // 🟢 Payment successfully completed
   // money accepted
   private const COMPLETED = 'completed';

   // 🟠 Payment partially refunded
   private const PARTIALLY_REFUNDED = 'partially_refunded';

   private const PARTIALLY_PAID = 'partially_paid';


   // 🔁 Payment fully refunded
   private const REFUNDED = 'refunded';

   // ❌ Payment rejected or failed
   private const FAILED = 'failed';

   // 🚫 Payment cancelled before completion
   private const CANCELLED = 'cancelled';
   
   private const SUCCEEDED = 'succeeded';

   private string $status;


    private function __construct(private string $paymentStatus)
    {
        $this->paymentStatus = $paymentStatus;
    }

   public static function handlePaymentStatus(string $status): self
   {
      return new self($status);
   }

  

   public static function pending(string $string=self::PENDING): self
   {
     
      if ($string !== self::PENDING) {
         return throw InvalidPendingException::invalidTransition($string, self::PENDING);
      }
      return new self(self::PENDING);
   }


   public static function partiallyPaid(string $string): self
   {
     
      if (!in_array($string,[self::PENDING , self::COMPLETED , self::BILLED])) {
         return throw InvalidPendingException::invalidTransition($string, self::PARTIALLY_PAID);
      }
      return new self(self::PARTIALLY_PAID);
   }

   public static function completed(string $string): self
   {
 
     
      if ( !in_array($string,[self::PENDING , self::COMPLETED])) {
         return throw InvalidPendingException::invalidTransition($string, self::COMPLETED);
      }
      return new self(self::COMPLETED);
   }
 

   
    public static function partiallyRefunded(string $status): self
    {
        if (!in_array($status, [
            self::COMPLETED,self::PARTIALLY_PAID
        ])) {
            throw InvalidPartiallyRefundedException::invalidTransition(
                $status,
                self::PARTIALLY_REFUNDED
            );
        }

        return new self(self::PARTIALLY_REFUNDED);
    }

    public static function paid(string $status): self
    {
        if (!in_array($status, [
            self::PENDING,
            self::PARTIALLY_PAID,
            self::COMPLETED,
            self::PARTIALLY_REFUNDED,
            self::REFUNDED,
            self::PAID,
            self::BILLED,
        ])) {
            throw InvalidPaidException::invalidTransition(
                $status,
                self::PAID
            );
        }

        return new self(self::REFUNDED);
    }

    public static function refunded(string $status): self
    {
        if (!in_array($status, [
            self::COMPLETED,
            self::PARTIALLY_REFUNDED
        ])) {
            throw InvalidRefundedException::invalidTransition(
                $status,
                self::REFUNDED
            );
        }

        return new self(self::REFUNDED);
    }

    public static function unpaid(string $status): self
    {
        if (!in_array($status, [
            self::PENDING,
        ])) {
            throw InvalidPendingException::invalidTransition(
                $status,
                self::REFUNDED
            );
        }

        return new self(self::REFUNDED);
    }

   
    public function value(): string
    {
        return $this->paymentStatus;
    }

    public function isPending(): bool
    {
        return $this->paymentStatus === self::PENDING;
    }

    public function isCompleted(): bool
    {
        return $this->paymentStatus === self::COMPLETED;
    }

    public function isPartiallyRefunded(): bool
    {
        return $this->paymentStatus === self::PARTIALLY_REFUNDED;
    }

    public function isRefunded(): bool
    {
        return $this->paymentStatus === self::REFUNDED;
    }

    public function getPaymentStatus(): string
    {
        return $this->paymentStatus;
    }

    public function __toString(): string
    {
        return $this->paymentStatus;
    }
}