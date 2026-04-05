<?php

namespace DentalOffice\AppointmentSchedulingBundle\Domain\Event;

class VisitEvent{
      private function __construct(
        
      private readonly int $visitId,
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
      int $visitId,
      int $invoiceId,
      int $medicalRecordId,
      int $patientId,
      int $practionerId,
      int $appoitmentId,
      array $payload,

   ):self
   {
    
         return new self( 
               $visitId,
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
       * Get the value of payload
       */ 
      public function getPayload():array
      {
            return $this->payload;
      }

      /**
       * Get the value of visitId
       */ 
      public function getVisitId():int
      {
            return $this->visitId;
      }
}