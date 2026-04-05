<?php

namespace  DentalOffice\InvoiceBundle\Application\UseCase;

use DentalOffice\InvoiceBundle\Application\Event\InvoiceUpdatedEvent;
use DentalOffice\InvoiceBundle\Domain\Entity\Invoice;
use DentalOffice\InvoiceBundle\Infrastructure\Persistence\Doctrine\Entity\InvoiceOrmEntity;
use DentalOffice\MedicalRecordBundle\Domain\Entity\MedicalRecord;
use DentalOffice\MedicalRecordBundle\Infrastructure\Persistence\Doctrine\Entity\MedicalRecordOrmEntity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: InvoiceUpdatedEvent::class)]
class UpdatingtingInvoiceOnUpdatingVisitsMedicalRecord
{
        public function __construct(
        private readonly EntityManagerInterface $entityManager
    )
    {
    }

    public function __invoke(InvoiceUpdatedEvent $event)
    {
        $medicalRecordId = $event->getMedicalRecordId();

        /** @var MedicalRecord $medicalRecord */
        $medicalRecord = $this->entityManager->getRepository(MedicalRecordOrmEntity::class)
            ->find($medicalRecordId);

        
        if (!$medicalRecord) {
            throw new \Exception("MedicalRecord not found");
        }


        $invoice = $this->entityManager->getRepository(InvoiceOrmEntity::class)->findOneBy([
            "medicalRecord" => $medicalRecord
        ]);
       
        
        
        $invoice->setInvoiceDate($invoice->getInvoiceDate());
        $invoice->setMedicalRecord($medicalRecord);
        $invoice->setTotalPaid($medicalRecord->getTotalPaid());
        $invoice->setTotalAmount($medicalRecord->getAgreedAmout());
        $invoice->setRemainingDue($medicalRecord->getRemainingDue());
        $invoice->setInvoiceNumber($invoice->generateInvoiceNumber());
        $invoice->setMedicalRecord($medicalRecord);
       
        $visits = $medicalRecord->getVisits();
       
        foreach ($visits as $visit) {
            foreach ($visit->getPayments() as $payment) {
                // Clone or use directly — assuming you want the same payment entity linked to invoice
                $invoice->addPayment($payment);
            }
        }

        
        $this->entityManager->persist($invoice);
        $this->entityManager->flush();
        
    }
}