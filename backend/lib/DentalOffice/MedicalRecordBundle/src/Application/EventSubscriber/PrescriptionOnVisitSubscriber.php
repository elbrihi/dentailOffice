<?php

namespace DentalOffice\MedicalRecordBundle\Application\EventSubscriber;

use DentalOffice\AppointmentSchedulingBundle\Domain\Event\VisitEvent;
use DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Entity\VisitOrmEntity;
use DentalOffice\MedicalRecordBundle\Infrastructure\Persistence\Doctrine\Entity\PrescriptionOrmEntity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PrescriptionOnVisitSubscriber implements EventSubscriberInterface
{

    public function __construct(
        private EntityManagerInterface $entityManager,
        private EventDispatcherInterface $dispatcher
    ) {}
    public static function getSubscribedEvents(): array
    {
        return [
            VisitEvent::class => 'addPrescrption'
        ];
    }

    public function addPrescrption(VisitEvent $event)
    {
        $prescriptions = $event->getPayload()[0]['visit']['prescriptions'];

    

        $visitOrmEntity = $this->entityManager->getRepository(VisitOrmEntity::class)
                              ->findOneBy(
                                [
                                    'id' => $event->getVisitId()
                                ]
        );
      
        for ($i=0; $i < sizeof($prescriptions) ; $i++) { 
           
            $prescriptionOrmEntity = new PrescriptionOrmEntity();
            
               
            $prescriptionOrmEntity->setDosage($prescriptions[$i]['dosage']);
            $prescriptionOrmEntity->setMedication($prescriptions[$i]['medication']);
            $prescriptionOrmEntity->setNotes($prescriptions[$i]['notes']);
            $prescriptionOrmEntity->setVisitOrmEntity($visitOrmEntity);

            
            $this->entityManager->persist($prescriptionOrmEntity);
           
        }

        $this->entityManager->flush();
    }
}