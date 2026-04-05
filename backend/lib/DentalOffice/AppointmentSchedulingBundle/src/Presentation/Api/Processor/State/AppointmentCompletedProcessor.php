<?php

namespace DentalOffice\AppointmentSchedulingBundle\Presentation\Api\Processor\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use DentalOffice\AppointmentSchedulingBundle\Application\Handler\ScheduleAppointmentHandlerInterface;
use DentalOffice\AppointmentSchedulingBundle\Domain\Aggregate\Appointment;
use DentalOffice\AppointmentSchedulingBundle\Domain\Event\AppointmentCompleted;
use DentalOffice\AppointmentSchedulingBundle\Domain\Service\AppointmentConflictChecker;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\AppointmentStatus;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\PatientId;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\PractitionerId;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\PurposeId;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\TimeSlot;
use DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Entity\AppointmentOrmEntity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AppointmentCompletedProcessor implements ProcessorInterface
{
        public function __construct(
                #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor,
        private ScheduleAppointmentHandlerInterface $handler,
        private AppointmentConflictChecker $appointmentConflictChecker,
        private Security $security, 
        private ClockInterface $clock,
        private EntityManagerInterface $entityManager,
        private EventDispatcherInterface $dispatcher
    )
    {
       
    }
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): AppointmentOrmEntity
    {
        // creating MedicalRecord in not exist then creating payment and invoice 

        $user =  $this->security->getUser();

        dd($user);
       
        $request = $context["request"];
        
        $request = json_decode($request->getContent(), true);
      
        $appointmentId = $uriVariables["appointmentId"];

        
        $orm =  $this->entityManager->getRepository(AppointmentOrmEntity::class)
                        ->findOneBy(
                        ["id" => $appointmentId]
                    );
       
        $timeSlot = new TimeSlot( 
                    $orm->getStart() ,
                    $orm->getEnd()
        );
         
        PatientId::fromInt($orm->getPatient()->getId());
        
        PractitionerId::fromInt($orm->getUser()->getId());

        PurposeId::fromString($orm->getReason());

        $confirmed = "confirmed";
        // $orm->getStatus()
        $appointment = Appointment::book(

           PatientId::fromInt($orm->getPatient()->getId()),
           $timeSlot,
           PractitionerId::fromInt($orm->getUser()->getId()),
           PurposeId::fromString( $orm->getStatus()),
           AppointmentStatus::completed($confirmed)

        );
       

        $orm->setStatus($appointment->getStatus()->getStatus());

        $appointmentData = $this->persistProcessor->process($orm, $operation, $uriVariables, $context);

      

        $payload = [$request,$user];
   
      
        $appointmentComplted= AppointmentCompleted::fromData(
            0,
            $orm->getId(),
            $orm->getPatient()->getId(),
            $orm->getUser()->getId(),
            $orm->getCreatedAt(),
            $uriVariables,
            $payload,

        );

     
   
        // MedicalRecord 
        $medicalRecord = $this->dispatcher->dispatch($appointmentComplted);

       
        // Visit

        // Update Medical Record 

        // payment

        // invoice 
        return  $orm;
        
        // updating MedicalRecord in exist and payment and invoice 
    }
    
}
