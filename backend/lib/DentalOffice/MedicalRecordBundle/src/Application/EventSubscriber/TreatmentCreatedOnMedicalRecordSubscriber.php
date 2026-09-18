<?php

namespace DentalOffice\MedicalRecordBundle\Application\EventSubscriber;

use DentalOffice\MedicalRecordBundle\Domain\Aggregate\Treatment;
use DentalOffice\MedicalRecordBundle\Domain\Event\TreatmentCreatedOnMedicalRecord;
use DentalOffice\MedicalRecordBundle\Domain\ValueObject\TreatmentStatus;
use DentalOffice\MedicalRecordBundle\Infrastructure\Persistence\Doctrine\Entity\MedicalRecordOrmEntity;
use DentalOffice\MedicalRecordBundle\Infrastructure\Persistence\Doctrine\Entity\TreatmentOrmEntity;
use DentalOffice\MedicalRecordBundle\Infrastructure\Persistence\Doctrine\Repository\MedicalRecordRepository;
use DentalOffice\MedicalRecordBundle\Infrastructure\Persistence\Doctrine\Repository\TreatmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TreatmentCreatedOnMedicalRecordSubscriber implements EventSubscriberInterface
{

  private MedicalRecordRepository $medicalRecordRepository;

  private TreatmentRepository $treatmentRepository;

  private EntityManagerInterface $entityManager;

  public function __construct(
    MedicalRecordRepository $medicalRecordRepository,
    TreatmentRepository $treatmentRepository,
    EntityManagerInterface $entityManager
  )
  {
    $this->medicalRecordRepository = $medicalRecordRepository;
    $this->treatmentRepository = $treatmentRepository;
    $this->entityManager = $entityManager;
  }

  public static function getSubscribedEvents(): array
  {
    return [
      TreatmentCreatedOnMedicalRecord::class => 'onTreatmentCreatedOnMedicalRecord'
    ];

  }

  public function onTreatmentCreatedOnMedicalRecord(TreatmentCreatedOnMedicalRecord $event): void
  {
    $this->createTreatmentOnMedicalRecord($event);
  }

  private function createTreatmentOnMedicalRecord(
    TreatmentCreatedOnMedicalRecord $event
  ): void
  {

    
    $medicalRecordPayload = $event->getPayload()[0]['medicalRecord']['treatments'];
    
  

     $medicalRecord = $this->medicalRecordRepository->findOneBy([
        'id' => $event->getMedicalRecordId()
     ]);

    $medicalRecord = $this->entityManager->getRepository(MedicalRecordOrmEntity::class)->findOneBy([
       'id' => $event->getMedicalRecordId()
    ]);
    $treatmentStatus = TreatmentStatus::planned();

     foreach($medicalRecordPayload as $treatment){


         $treatmentOrmEntity = new TreatmentOrmEntity();
         $treatment = Treatment::createTreatmentPlan(
             0,
            $treatment['description'],
            $treatmentStatus, 
            $treatment['type']   
         );


         $treatmentOrmEntity->setDescription($treatment->getDescription());
         $treatmentOrmEntity->setStatus($treatment->getStatus()->getStatus());
         $treatmentOrmEntity->setType($treatment->getType());
         $treatmentOrmEntity->setMedicalRecordOrmEntity($medicalRecord);
         $this->entityManager->persist($treatmentOrmEntity);
         $this->entityManager->flush();
         
     }
     
  }

  
  
  
}