<?php

namespace DentalOffice\AppointmentSchedulingBundle\Tests\Functional\Domain\ValueObject;

use DentalOffice\AppointmentSchedulingBundle\Domain\Exception\ConfirmedException;
use DentalOffice\AppointmentSchedulingBundle\Domain\Exception\ReschouldException;
use DentalOffice\AppointmentSchedulingBundle\Domain\Exception\ShowException;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\AppointmentStatus;
use DentalOffice\AppointmentSchedulingBundle\Domain\Exception\CancelledException;
use DentalOffice\AppointmentSchedulingBundle\Domain\Exception\CompletedException;
use PHPUnit\Framework\TestCase;

class AppointmentStatusTest extends TestCase
{

   private const SCHEDULED = 'scheduled';
   private const RESCHEDULED = 'rescheduled';
   private const CONFIRMED = 'confirmed';
   private const NO_SHOW = 'no_show';
   private const CANCELLED = 'cancelled';
   private const COMPLETED = 'completed';
   protected function setUp(): void
   {
       parent::setUp(); 
   }

   public function test_appointment_is_scheduled()
   {
       $status = AppointmentStatus::scheduled();
    
       $this->assertInstanceOf(AppointmentStatus::class ,$status);
                         
       $this->assertSame($status->getStatus(),self::SCHEDULED);
   }

    public function test_appointment_is_no_show()
    { 
       $status = AppointmentStatus::noShow(self::CONFIRMED);
    
       $this->assertInstanceOf(AppointmentStatus::class ,$status);
                         
       $this->assertSame($status->getStatus(),self::NO_SHOW);
    }

    public function test_appointment_is_show()
    {
         
        try {
         AppointmentStatus::noShow(self::SCHEDULED);
        } 
         catch (ShowException $e) {
            
            $this->assertSame($e->getMessage(),"Only confirmed appointments can become no-show");

        }
             
   }

//    public function test_can_t_reschould_appointment()
//    {
//         try {
//             AppointmentStatus::reschould("test");
//         } catch (ReschouldException $e) {
//             //throw $th;

//             $this->assertSame($e->getMessage(),"to reschoulde new appointment have be confirmed or scheduled");
//         }
//    }

    public function test_can_reschould_appointment()
   {
       $reschouled = AppointmentStatus::reschould(self::CONFIRMED);

       $this->assertInstanceOf(AppointmentStatus::class,$reschouled);

       $this->assertSame($reschouled->getStatus(),self::RESCHEDULED);

   }

   public function test_can_t_appointment_confirmed()
   {

       try {
        AppointmentStatus::confirmed(self::CANCELLED);

       } catch (ConfirmedException $e) 
       {
            $this->assertSame($e->getMessage(),"to confirme the appoinment schould be shoudled first");
       }
   }

   public function test_can_appointment_confirmed()
   {

       $confirmed = AppointmentStatus::confirmed(self::SCHEDULED);

       $this->assertInstanceOf(AppointmentStatus::class,$confirmed);

       $this->assertSame($confirmed->getStatus(),self::CONFIRMED);
   }

    public function test_can_appointment_cancelled()
    {

       $confirmed = AppointmentStatus::cancelled(self::CONFIRMED);

       $this->assertInstanceOf(AppointmentStatus::class,$confirmed);

       $this->assertSame($confirmed->getStatus(),self::CANCELLED);
    }

    public function test_can_t_cancel_appointment()
    {

        try {
           AppointmentStatus::cancelled("test");
        } catch (CancelledException $e) {
           $this->assertSame($e->getMessage(),"to cannel appointment should be confirmed first");
        }
    }


    public function test_can_appointment_completed()
    {

       $completed = AppointmentStatus::completed(self::CONFIRMED);

       $this->assertInstanceOf(AppointmentStatus::class, $completed);

       $this->assertSame($completed ->getStatus(),self::COMPLETED);
    }

    public function test_can_t_complete_appointment()
    {

        try {
            AppointmentStatus::completed("test");
        } catch (CompletedException $e) {


            $json = json_decode($e->getMessage(), true);

            $this->assertArrayHasKey('type', $json);
            $this->assertSame(409, $json['status']);
           // $this->assertStringContainsString('already has an appointment', $json['detail']);
        }
        
    }
    

}