<?php

namespace DentalOffice\PaymentsBundle\Tests\Infrastructure\Persistence\Doctrine\Processor\State;

use DentalOffice\PaymentsBundle\Tests\PaymentProcessorTest;


class PaymentPostProcessorTest extends PaymentProcessorTest
{
   public function testPaymentPostProcessor(): void
   {
      $this->saveUser();
      $this->savePatient();
      $this->saveAppointment();
      $this->saveMedicalRecord();
      $this->saveInvoice();
      $this->savePrescption();
      $this->saveInvoiceItems();
   }
}