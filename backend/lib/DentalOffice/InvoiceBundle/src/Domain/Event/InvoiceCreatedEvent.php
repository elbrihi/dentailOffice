<?php

namespace DentalOffice\InvoiceBundle\Domain\Event;


class InvoiceCreatedEvent
{
   

   private function __construct(
      private readonly int $invoiceId,
      private readonly int $medicalRecordId,
      private readonly int $patientId,
      private readonly int $practionerId,
      private readonly int $appoitmentId,
      private readonly  array $payload,
   )
   {
   }

   public static function initialInvoice(
      int $invoiceId,
      int $medicalRecordId,
      int $patientId,
      int $practionerId,
      int $appoitmentId,
      array $payload,

   ):self
   {
         return new self( 
               $invoiceId,
               $medicalRecordId,
               $patientId,
               $practionerId,
               $appoitmentId,
               $payload,
           
         )
         
         
         ;
   }

   /**
    * Get the value of invoiceId
      */ 
   public function getInvoiceId():int
   {
            return $this->invoiceId;
   }

   /**
    * Get the value of medicalRecordId
    */ 
   public function getMedicalRecordId():int
   {
         return $this->medicalRecordId;
   }

   /**
    * Get the value of patientId
    */ 
   public function getPatientId():int
   {
         return $this->patientId;
   }

   /**
    * Get the value of practionerId
    */ 
   public function getPractionerId():int
   {
         return $this->practionerId;
   }

   /**
    * Get the value of appoitmentId
    */ 
   public function getAppoitmentId():int
   {
         return $this->appoitmentId;
   }

   /**
    * Get the value of payload
    */ 
   public function getPayload():array
   {
         return $this->payload;
   }
}