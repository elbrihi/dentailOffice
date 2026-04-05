<?php

namespace DentalOffice\MedicalRecordBundle\Application\EventSubscriber;

use DateTimeImmutable;
use DentalOffice\AppointmentSchedulingBundle\Domain\Aggregate\Visit;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\VisitAmountPaid;
use DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Entity\AppointmentOrmEntity;
use DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Entity\VisitOrmEntity;
use DentalOffice\InvoiceBundle\Domain\Event\InvoiceCreatedEvent;
use DentalOffice\MedicalRecordBundle\Domain\Event\MedicalRecordCreated;
use DentalOffice\MedicalRecordBundle\Infrastructure\Persistence\Doctrine\Entity\MedicalRecordOrmEntity;
use DentalOffice\MedicalRecordBundle\Infrastructure\Persistence\Doctrine\Entity\PrescriptionOrmEntity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class MedicalRecordCreatedOnVisitSubscriber  implements EventSubscriberInterface
{
     public function __construct(
        private EntityManagerInterface $entityManager,
        private EventDispatcherInterface $dispatcher
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            InvoiceCreatedEvent::class => ['createVisit',0]
        ];
    }

    public function createVisit(InvoiceCreatedEvent $event)
    {
        dd("hi");
        $visitInpout =$event->getPayload()["request"]["visit"];
        $medicalRecordInput = $event->getPayload()["request"]["medicalRecord"];

        
        $medicalRecord = $this->entityManager->getRepository(MedicalRecordOrmEntity::class)
                              ->findOneBy(
                                [
                                    'id' => $event->medicalRecordId
                                ]
        );

        $appointment = $this->entityManager->getRepository(AppointmentOrmEntity::class)
                             ->findOneBy([
                                'id' =>$event->getAppointmentId()
        ]);

        $createdAt = new DateTimeImmutable();

        $user = $medicalRecord->getCreatedBy();
      
     
     
        
        $visit = Visit::createVisit(
            VisitAmountPaid::fromFloatPositive($visitInpout["amount_paid"]),
        );

        $visitOrmEntity = new VisitOrmEntity();

        $visitOrmEntity->setCreatedAt( $createdAt);
        $visitOrmEntity->setModifiedAt( $createdAt);
        $visitOrmEntity->setAmountPaid($visit->getVisitAmountPaid()->getAmountPaid());
        $visitOrmEntity->setNotes($visitInpout ["notes"]);
        $visitOrmEntity->setCreatedAt($createdAt);
        $visitOrmEntity->setCreatedBy($user);
        $visitOrmEntity->setNotes($visitInpout["notes"]);
        $visitOrmEntity->setModifiedBy($user);
        $visitOrmEntity->setMedicalRecord($medicalRecord );
        $visitOrmEntity->setStart($appointment->getStart());
        $visitOrmEntity->setEnd($appointment->getEnd());
        $visitOrmEntity->setModifiedBy($user);
        $visitOrmEntity->setStatus($appointment->getStatus());
        $visitOrmEntity->setType($visitInpout["type"]);
        $visitOrmEntity->setAppointment($appointment);
        
        $this->entityManager->persist($visitOrmEntity);

        $this->entityManager->flush();

        $prescriptient = new PrescriptionOrmEntity();
        
        $prescriptients = $visitInpout["prescriptions"];
 
        foreach ($prescriptients as $prescriptient) {

            $prescriptientOrmEntity = new PrescriptionOrmEntity();

            $prescriptientOrmEntity ->setMedication($prescriptient["medication"]);

            $prescriptientOrmEntity->setDosage($prescriptient["dosage"]);

            $prescriptientOrmEntity->setNotes($prescriptient["notes"]);

            $prescriptientOrmEntity->setVisitOrmEntity($visitOrmEntity);

            $this->entityManager->persist($prescriptientOrmEntity);

        }

        $this->entityManager->flush();      

        $medicalRecordId = $medicalRecord->getId();
        $patient = $medicalRecord->getPatient();

        $medicalRecordEvent = MedicalRecordCreated::medicalRecordData(
            $medicalRecordId ,
            $patient->getId(),
            $medicalRecord->getUser()->getId(),
            $appointment->getId(),
            $event->getPayload()
        );
     
        $this->dispatcher->dispatch($medicalRecordEvent);

    }

}