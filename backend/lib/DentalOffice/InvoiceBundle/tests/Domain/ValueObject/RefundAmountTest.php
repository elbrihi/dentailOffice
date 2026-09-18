<?php

namespace App\Tests\Domain\ValueObject;

use PHPUnit\Framework\TestCase;
use DentalOffice\InvoiceBundle\Domain\Exception\InvalidRefundAmountException;
use DentalOffice\InvoiceBundle\Domain\ValueObject\RefundAmount;

class RefundAmountTest extends TestCase
{
   public function test_refund_amount_is_positive(): void
   {

      $refundAmount = RefundAmount::fromPositiveRefundAmount(100);

      $this->assertSame(100.0, $refundAmount->refundAmount());
   }

   public function test_refund_amount_is_zero(): void
   {
      $refundAmount = RefundAmount::fromZeroRefundAmount();
      $this->assertSame(0.0, $refundAmount->refundAmount());
   }

   public function test_refund_amount_is_invalid(): void
   {
      $this->expectException(InvalidRefundAmountException::class);
      RefundAmount::fromPositiveRefundAmount(-100.0);
   }
}