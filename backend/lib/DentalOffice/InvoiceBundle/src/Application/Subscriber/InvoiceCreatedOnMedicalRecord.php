<?php

namespace DentalOffice\InvoiceBundle\Application\Subscriber;

use DateTimeImmutable;
use DentalOffice\InvoiceBundle\Domain\Aggregate\InvoiceItem;
use DentalOffice\InvoiceBundle\Domain\Event\InvoiceCreated;
use DentalOffice\InvoiceBundle\Domain\Event\InvoiceCreatedEvent;
use DentalOffice\InvoiceBundle\Infrastructure\Persistence\Doctrine\Entity\InvoiceItemOrmEntity;
use DentalOffice\InvoiceBundle\Infrastructure\Persistence\Doctrine\Entity\InvoiceOrmEntity;
use DentalOffice\MedicalRecordBundle\Domain\Aggregate\MedicalRecord;
use DentalOffice\MedicalRecordBundle\Domain\Event\MedicalRecordCreated;
use DentalOffice\MedicalRecordBundle\Domain\ValueObject\MedicalRecordAgreedAmount;
use DentalOffice\MedicalRecordBundle\Domain\ValueObject\MedicalRecordChiefComplaint;
use DentalOffice\MedicalRecordBundle\Domain\ValueObject\MedicalRecordId;
use DentalOffice\MedicalRecordBundle\Infrastructure\Persistence\Doctrine\Entity\MedicalRecordOrmEntity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class InvoiceCreatedOnMedicalRecord implements EventSubscriberInterface 
{
   private const TOTAL_PAID = 0;
   private const REMAINING_DUE = 0;
   
   public function __construct(
        private EntityManagerInterface $entityManager,
        private EventDispatcherInterface $dispatcher
    )
   {
    
   }
   public static function getSubscribedEvents(): array
   {
        return [
            MedicalRecordCreated::class => ['createInvoice',100]
        ];
   }

   public function createInvoice(MedicalRecordCreated $event)
   {
       
        $invoice = new InvoiceOrmEntity();

        $createdAt = new DateTimeImmutable();

        $medicalRecord = $this->entityManager->getRepository(MedicalRecordOrmEntity::class)
                                ->findBy(
                                    ['id' => $event->getMedicalRecordId()]
                            )[0];

        $invoice->setInvoiceDate( $createdAt);
        $invoice->setMedicalRecord($medicalRecord );
        $invoice->setInvoiceNumber($invoice->generateInvoiceNumber());
        $invoice->setAgreedAmount($medicalRecord->getAgreedAmount());
        $invoice->setTotalAmount($medicalRecord->getAgreedAmount());
        $invoice->setRemainingDue($medicalRecord->getAgreedAmount());
        $invoice->setTotalPaid(static::TOTAL_PAID);
        $invoice->setTotalAmount($medicalRecord->getAgreedAmount());
       
        $this->entityManager->persist( $invoice);
        $this->entityManager->flush();

 
   
        $items = $event->getPayload()[0]['visit']['items'] ;

       
        for($i =0 ; $i < sizeof( $items);$i++)
        {
            $invoiceItem = new InvoiceItemOrmEntity();

            $itemStatus = InvoiceItem::invoice
            (
                0,
                $items[$i]['description'],
                $items[$i]['amount'],
                0,
               

            )->getStatus()::planned();
            $invoiceItem->setAmount($items[$i]['amount']);

            $invoiceItem->setDescription($items[$i]['description']);

            $invoiceItem->setInvoiceOrmEntity($invoice);

            $invoiceItem->setStatus($itemStatus->getStatus());
            //dump($invoiceItem);
            $this->entityManager->persist($invoiceItem);
            
        }

      
        $medicalRecordId  = $medicalRecord->getId();

        $this->entityManager->flush();
          //  dd( $invoiceItem);
            $medicalRecord = MedicalRecord::medicalRecord(
            MedicalRecordId::toInt($medicalRecordId ),
            MedicalRecordChiefComplaint::chiefComplaint($medicalRecord->getChiefComplaint()),
            MedicalRecordAgreedAmount::fromNumeric($medicalRecord->getAgreedAmount())
        );

      
        // $uriVariables = $event->getUriVariables();

         $payload =  $event->getPayload();


        $invoiceCreatedEvent = InvoiceCreated::initialInvoice(
            $invoice->getId(),
            $medicalRecordId ,
            $event->getPatientId(),
            $event->getPractionerId(),
            $event->getAppointmentId(),
            $payload
        );


        $this->dispatcher->dispatch($invoiceCreatedEvent );
              
   }
}