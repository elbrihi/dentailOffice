<?php

namespace DentalOffice\MedicalRecordBundle\Application\EventSubscriber;
use DateTimeImmutable;
use DentalOffice\AppointmentSchedulingBundle\Domain\Event\AppointmentCompleted;
use DentalOffice\MedicalRecordBundle\Domain\Aggregate\MedicalRecord;
use DentalOffice\MedicalRecordBundle\Domain\Event\MedicalRecordCreated;
use DentalOffice\MedicalRecordBundle\Domain\ValueObject\MedicalRecordAgreedAmount;
use DentalOffice\MedicalRecordBundle\Domain\ValueObject\MedicalRecordChiefComplaint;
use DentalOffice\MedicalRecordBundle\Domain\ValueObject\MedicalRecordId;
use DentalOffice\MedicalRecordBundle\Infrastructure\Persistence\Doctrine\Entity\MedicalRecordOrmEntity;
use DentalOffice\PatientBundle\Domain\Entity\Patient;
use DentalOffice\UserBundle\Domain\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


class MedicalRecordCreatedSubscriber implements EventSubscriberInterface
{
     public function __construct(
        private EntityManagerInterface $entityManager,
         private EventDispatcherInterface $dispatcher
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            AppointmentCompleted::class => 'onAppointmentCompleted'
        ];
    }

    public function onAppointmentCompleted(AppointmentCompleted $event): void
    {
   
      
       
        $medicalRecordInput  = $event->getPayload()[0]["medicalRecord"];
      
        
        $user = $event->getPayload()[1];
     
        $userId = $user->getId();

        $user = $this->entityManager
            ->getRepository(User::class)
            ->find($userId);
        $patient = $this->entityManager->getRepository(Patient::class)
                    ->findOneBy([
                        'id' => $event->getPatientId()
        ]);
      

        $medicalRecord = MedicalRecord::medicalRecord(
            MedicalRecordId::toInt(0),
            MedicalRecordChiefComplaint::chiefComplaint($medicalRecordInput['chief_complaint']),
            MedicalRecordAgreedAmount::fromNumeric($medicalRecordInput['agreedAmount'])
        ) ;

        

        $createdAt =  new DateTimeImmutable();

        $medicalRecordOrmEntity = new MedicalRecordOrmEntity();

        $medicalRecordOrmEntity->setChiefComplaint($medicalRecord->getMedicalRecodChiefComplaint()->getValue());

        
        $medicalRecordOrmEntity->setAgreedAmount($medicalRecord->getAgreedAmout()->getAgreedAmountValue());

        $medicalRecordOrmEntity->setClinicalDiagnosis($medicalRecordInput['clinical_diagnosis']);

      // 
        //treatment_plan
        
        $medicalRecordOrmEntity->setTreatmentPlan($medicalRecordInput ['treatment_plan']);

        $medicalRecordOrmEntity->setNotes($medicalRecordInput ['notes']);



        $medicalRecordOrmEntity->setCreatedBy($user);
        
        $medicalRecordOrmEntity->setModifiedAt($createdAt);

        $medicalRecordOrmEntity->setCreatedAt($createdAt);

        $medicalRecordOrmEntity->setPatient($patient);


        $medicalRecordOrmEntity->setTotalPaid(0);

        $medicalRecordOrmEntity->setRemainingDue(0);
      
        $medicalRecordOrmEntity->setUser($user);
 
        $this->entityManager->persist($medicalRecordOrmEntity);

        $this->entityManager->flush();

        $medicalRecordId = $medicalRecordOrmEntity->getId();


       
    
   
       
        $medicalRecord = MedicalRecord::medicalRecord(
            MedicalRecordId::toInt($medicalRecordId ),
            MedicalRecordChiefComplaint::chiefComplaint($medicalRecordOrmEntity->getChiefComplaint()),
            MedicalRecordAgreedAmount::fromNumeric($medicalRecordOrmEntity->getAgreedAmount())
        );

        $uriVariables = $event->getUriVariables();

        $payload =  $event->getPayload();


       
        $medicalRecordEvent = MedicalRecordCreated::medicalRecordData(
            $medicalRecordId ,
            $patient->getId(),
            $medicalRecordOrmEntity->getUser()->getId(),
            $event->getAppointmentId(),
            $payload
        );
   
        // intial invoice 

       

      
        
        $this->dispatcher->dispatch($medicalRecordEvent);
 
    }

}