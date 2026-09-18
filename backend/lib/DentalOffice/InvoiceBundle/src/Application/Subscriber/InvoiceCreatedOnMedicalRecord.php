<?php

namespace DentalOffice\InvoiceBundle\Application\Subscriber;

use DateTimeImmutable;
use DentalOffice\InvoiceBundle\Domain\Aggregate\Invoice;
use DentalOffice\InvoiceBundle\Domain\Aggregate\InvoiceItem;
use DentalOffice\InvoiceBundle\Domain\Event\InvoiceCreated;
use DentalOffice\InvoiceBundle\Domain\Event\InvoiceCreatedEvent;
use DentalOffice\InvoiceBundle\Domain\ValueObject\AgreedAmount;
use DentalOffice\InvoiceBundle\Domain\ValueObject\InvoiceStatus;
use DentalOffice\InvoiceBundle\Domain\ValueObject\InvoiceTotalAmount;
use DentalOffice\InvoiceBundle\Domain\ValueObject\RemainingDue;
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
    ) {}
    public static function getSubscribedEvents(): array
    {
        return [
            MedicalRecordCreated::class => ['createInvoice', 100]
        ];
    }

    public function createInvoice(MedicalRecordCreated $event)
    {




        $invoiceEntity = new InvoiceOrmEntity();

        $createdAt = new DateTimeImmutable();

        $medicalRecord = $this->entityManager->getRepository(MedicalRecordOrmEntity::class)
            ->findBy(
                ['id' => $event->getMedicalRecordId()]
            )[0];


        $totalAmount   = InvoiceTotalAmount::fromPositifTotlaAmount($medicalRecord->getAgreedAmount());
        $remainingDue  = RemainingDue::fromPositifRemainingDue($medicalRecord->getAgreedAmount());
        $agreedAmount  = AgreedAmount::fromPositifAgreedAmount($medicalRecord->getAgreedAmount());

        $invoiceStatus = InvoiceStatus::handleInvoiceStatus(
            $remainingDue->getRemainingDue(),
            $totalAmount->getTotalAmount(),
            $agreedAmount->agreedAmount()
        );

        $invoice = Invoice::generateInvoice(
            $totalAmount,
            $remainingDue,
            $agreedAmount,
            $invoiceStatus,

        );

        $invoiceEntity->setInvoiceDate($createdAt);
        $invoiceEntity->setMedicalRecord($medicalRecord);
        $invoiceEntity->setInvoiceNumber($invoiceEntity->generateInvoiceNumber());
        $invoiceEntity->setAgreedAmount($agreedAmount->agreedAmount());
        $invoiceEntity->setTotalAmount($totalAmount->getTotalAmount());
        $invoiceEntity->setRemainingDue($remainingDue->getRemainingDue());
        $invoiceEntity->setTotalPaid(0);
        $invoiceEntity->setStatus($invoiceStatus->getInvoiceStatus());

        $this->entityManager->persist($invoiceEntity);
        $this->entityManager->flush();



        $items = $event->getPayload()[0]['visit']['items'];


        for ($i = 0; $i < sizeof($items); $i++) {
            $invoiceItem = new InvoiceItemOrmEntity();

            $itemStatus = InvoiceItem::invoice
            (
                0,
                $items[$i]['description'],
                $items[$i]['amount'],
                0,

            );


            $invoiceItem->setAmount($items[$i]['amount']);

            $invoiceItem->setDescription($items[$i]['description']);

            $invoiceItem->setStatus($itemStatus->getStatus()::planned()->getStatus());

            $invoiceItem->setInvoiceOrmEntity($invoiceEntity);

            $this->entityManager->persist($invoiceItem);
        }


        $medicalRecordId  = $medicalRecord->getId();

        $this->entityManager->flush();


        $medicalRecord = MedicalRecord::medicalRecord(
            MedicalRecordId::toInt($medicalRecordId),
            MedicalRecordChiefComplaint::chiefComplaint($medicalRecord->getChiefComplaint()),
            MedicalRecordAgreedAmount::fromNumeric($medicalRecord->getAgreedAmount())
        );



        // $uriVariables = $event->getUriVariables();

        $payload =  $event->getPayload();

        $invoiceCreatedEvent = InvoiceCreated::initialInvoice(
            $invoiceEntity->getId(),
            $medicalRecordId,
            $event->getPatientId(),
            $event->getPractionerId(),
            $event->getAppointmentId(),
            $payload
        );;



        //  $this->dispatcher->dispatch($invoiceCreatedEvent );

    }
}
