<?php


namespace DentalOffice\PaymentsBundle\Tests\Domain\ValueObject;

use DentalOffice\PaymentsBundle\Domain\Exception\InvalidBilledException;
use DentalOffice\PaymentsBundle\Domain\Exception\InvalidPaidException;
use DentalOffice\PaymentsBundle\Domain\Exception\InvalidPartiallyPaidException;
use DentalOffice\PaymentsBundle\Domain\Exception\InvalidPartiallyRefundedException;
use DentalOffice\PaymentsBundle\Domain\Exception\InvalidPendingException;
use DentalOffice\PaymentsBundle\Domain\Exception\InvalidRefundedException;
use DentalOffice\PaymentsBundle\Domain\ValueObject\PaymentStatus;
use PHPUnit\Framework\TestCase;

class PaymentStatusTest extends TestCase
{

   private const UNBILLED = 'unbilled';
   private const BILLED = 'billed';
   private const PARTIALLY_PAID = 'partially_paid';
   private const PARTIALLY_REFUNDED = 'partially_refunded';
   private const PAID = 'paid';
   private const REFUNDED = 'refunded';
   private const PENDING = 'pending';
   private const COMPLETED = 'completed';

    /**
     * @test
     */
    public function it_should_create_a_partially_refunded_status_from_completed_or_partially_paid()
    {
        $paymentStatus = PaymentStatus::partiallyRefunded(self::COMPLETED);
        $this->assertEquals(self::PARTIALLY_REFUNDED, $paymentStatus->getPaymentStatus());

        $paymentStatus = PaymentStatus::partiallyRefunded(self::PARTIALLY_PAID);
        $this->assertEquals(self::PARTIALLY_REFUNDED, $paymentStatus->getPaymentStatus());
    }

    /**
     * @test
     */
    public function it_should_throw_an_invalid_transition_exception_when_partially_refunded()
    {
        try {
            $paymentStatus = PaymentStatus::partiallyRefunded(self::PARTIALLY_REFUNDED);
        } catch (InvalidPartiallyRefundedException $e) {
            $this->assertEquals(InvalidPartiallyRefundedException::invalidTransition(self::PARTIALLY_REFUNDED, self::PARTIALLY_REFUNDED)->getMessage(), $e->getMessage());
        }
    }
     
   /**
    * @test
    */
   public function test_it_should_create_a_pending_status_from_unpaid()
   {
      $paymentStatus = PaymentStatus::pending();
      $this->assertEquals(self::PENDING, $paymentStatus->getPaymentStatus());
   }

   
   public function test_it_should_create_a_completed_status_from_pending()
   {
      $paymentStatus = PaymentStatus::completed(self::PENDING);
      $this->assertEquals(self::COMPLETED, $paymentStatus->getPaymentStatus());
   }

   
   public function test_it_should_throw_an_invalid_transition_exception_when_pending()
   {
      try {
         $paymentStatus = PaymentStatus::pending(self::PENDING);
      } catch (InvalidPendingException $e) {
         $this->assertEquals(InvalidPendingException::invalidTransition(self::PENDING, self::PENDING)->getMessage(), $e->getMessage());
      }
   }

   public function test_it_should_create_completed_from_pending()
   {
         $paymentStatus = PaymentStatus::completed(self::PENDING);
         $this->assertEquals(self::COMPLETED, $paymentStatus->getPaymentStatus());
   }

   public function test_it_should_throw_an_invalid_transition_exception_when_completed()
   {
      try {
         $paymentStatus = PaymentStatus::completed(self::PENDING);
      } catch (InvalidPendingException $e) {
         $this->assertEquals(InvalidPendingException::invalidTransition(
            self::PENDING, 
            self::PENDING
         )->getMessage(), $e->getMessage());
      }
   }

   public function test_it_should_partially_refunded_from_completed()
   {
      $paymentStatus = PaymentStatus::partiallyRefunded(self::COMPLETED);
      $this->assertEquals(self::PARTIALLY_REFUNDED, $paymentStatus->getPaymentStatus());
   }
  


   public function test_it_should_throw_an_invalid_transition_exception_when_partially_paid()
   {
      try {
         $paymentStatus = PaymentStatus::partiallyPaid(self::PARTIALLY_PAID);
      } catch (InvalidPendingException $e) {
         $this->assertEquals(InvalidPendingException::invalidTransition(
            self::PARTIALLY_PAID, 
            self::PARTIALLY_PAID
         )->getMessage(), $e->getMessage());
      }
   }

   public function test_it_should_create_a_refunded_status_from_completed_or_partially_refunded()
   {
      $paymentStatus = PaymentStatus::refunded(self::COMPLETED);
      $this->assertEquals(self::REFUNDED, $paymentStatus->getPaymentStatus());

      $paymentStatus = PaymentStatus::refunded(self::PARTIALLY_REFUNDED);
      $this->assertEquals(self::REFUNDED, $paymentStatus->getPaymentStatus());
   }

   public function test_is_should_throw_an_invalid_transition_sxception_when_partially_refunded_from_refunded()
   {
      try {
         $paymentStatus = PaymentStatus::partiallyRefunded(self::REFUNDED);
      } catch (InvalidPartiallyRefundedException $e) {
         $this->assertEquals(InvalidPartiallyRefundedException::invalidTransition(
            self::REFUNDED, 
            self::PARTIALLY_REFUNDED
         )->getMessage(), $e->getMessage());
      }
   }



   /**
    * @test
    */
   public function it_should_create_a_unbilled_status()
   {
      $paymentStatus = PaymentStatus::unbilled();
      $this->assertEquals('unbilled', $paymentStatus->getPaymentStatus());
   }

   public function test_it_should_create_a_billed_status_from_unbilled()
   {
      $paymentStatus = PaymentStatus::billed(self::UNBILLED);
      $this->assertEquals('billed', $paymentStatus->getPaymentStatus());
   }
   
   public function test_it_should_throw_an_invalid_transition_exception_when_billed()
   {
      try {
         $paymentStatus = PaymentStatus::billed(self::BILLED);
      } catch (InvalidBilledException $e) {
         $this->assertEquals(InvalidBilledException::invalidTransition(self::BILLED, self::BILLED)->getMessage(), $e->getMessage());
      }
   }

   
   /**
    * @tests
    */
   public function test_it_should_create_a_partially_paid_status()
   {
      $paymentStatus = PaymentStatus::partiallyPaid(self::BILLED);
      $this->assertEquals(self::PARTIALLY_PAID, $paymentStatus->getPaymentStatus());
   }


} 