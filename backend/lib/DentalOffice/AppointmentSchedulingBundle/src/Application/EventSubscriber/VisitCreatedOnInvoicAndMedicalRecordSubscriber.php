<?php

namespace DentalOffice\AppointmentSchedulingBundle\Application\EventSubscriber;

use DateTimeImmutable;
use DentalOffice\AppointmentSchedulingBundle\Domain\Aggregate\Visit;
use DentalOffice\AppointmentSchedulingBundle\Domain\Event\VisitCreated;
use DentalOffice\AppointmentSchedulingBundle\Domain\Event\VisitEvent;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\VisitAmountPaid;
use DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Entity\AppointmentOrmEntity;
use DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Entity\VisitOrmEntity;
use DentalOffice\InvoiceBundle\Domain\Event\InvoiceCreated;
use DentalOffice\MedicalRecordBundle\Infrastructure\Persistence\Doctrine\Entity\MedicalRecordOrmEntity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class VisitCreatedOnInvoicAndMedicalRecordSubscriber implements EventSubscriberInterface
{
     public function __construct(
        private EntityManagerInterface $entityManager,
        private EventDispatcherInterface $dispatcher
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
             InvoiceCreated::class => 'createVisit'
        ];
    }

    public function createVisit(InvoiceCreated $event)
    {
        
    
      

        $visitInpout =$event->getPayload()[0]["visit"];
        $medicalRecordInput = $event->getPayload()[0]["medicalRecord"];

        
        $medicalRecord = $this->entityManager->getRepository(MedicalRecordOrmEntity::class)
                              ->findOneBy(
                                [
                                    'id' => $event->getMedicalRecordId()
                                ]
        );

        $appointment = $this->entityManager->getRepository(AppointmentOrmEntity::class)
                             ->findOneBy([
                                'id' =>$event->getAppoitmentId()
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

        $visitCreated = VisitEvent::initialInvoice(
                            $visitOrmEntity->getId(),
                            $event->getInvoiceId(),
                            $event->getMedicalRecordId(),
                            $event->getPatientId(),
                            $event->getPractionerId(),
                            $event->getAppoitmentId(),
                            $event->getPayload()
        );
        
     

        $this->dispatcher->dispatch($visitCreated);
        // $prescriptient = new PrescriptionOrmEntity();
        
        // $prescriptients = $visitInpout["prescriptions"];
 
        // foreach ($prescriptients as $prescriptient) {

        //     $prescriptientOrmEntity = new PrescriptionOrmEntity();

        //     $prescriptientOrmEntity ->setMedication($prescriptient["medication"]);

        //     $prescriptientOrmEntity->setDosage($prescriptient["dosage"]);

        //     $prescriptientOrmEntity->setNotes($prescriptient["notes"]);

        //     $prescriptientOrmEntity->setVisitOrmEntity($visitOrmEntity);

        //     $this->entityManager->persist($prescriptientOrmEntity);

        // }
        // // vidsit Id 

        // $payments = $visitInpout["payments"];
        
        // // foreach ( $payments as $payment) {

        // //     $paymentOrmEntity = new PaymentOrmEntity();
        // //     $paymentOrmEntity->setMethod($payment["method"]);
        // //     $paymentOrmEntity->setPaymentDate(new DateTimeImmutable($payment["payment_date"]) );
        // //     $paymentOrmEntity->setVisit($visitOrmEntity);

        // //     $this->entityManager->persist($paymentOrmEntity);
        
        // // }
        
        // $this->entityManager->flush();

        

    }
    
}

