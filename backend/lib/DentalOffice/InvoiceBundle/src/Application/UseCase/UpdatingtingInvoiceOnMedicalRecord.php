<?php

namespace DentalOffice\InvoiceBundle\Application\UseCase;

use DentalOffice\InvoiceBundle\Application\Event\InvoiceCreatedEvent;
use DentalOffice\InvoiceBundle\Domain\Entity\Invoice;
use DentalOffice\InvoiceBundle\Infrastructure\Persistence\Doctrine\Entity\InvoiceOrmEntity;
use DentalOffice\MedicalRecordBundle\Domain\Entity\MedicalRecord;
use DentalOffice\MedicalRecordBundle\Infrastructure\Persistence\Doctrine\Entity\MedicalRecordOrmEntity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: InvoiceCreatedEvent::class)]
class UpdatingtingInvoiceOnMedicalRecord
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    )
    {
    
    }

    public function __invoke(InvoiceCreatedEvent $event)
    {
        $medicalRecordId = $event->getMedicalRecordId();

       
        /** @var MedicalRecord $medicalRecord */
        $medicalRecord = $this->entityManager->getRepository(MedicalRecordOrmEntity::class)
            ->find($medicalRecordId);

        if (!$medicalRecord) {
            throw new \Exception("MedicalRecord not found");
        }


        $invoices = $medicalRecord->getInvoice();

        if (!$invoices->isEmpty()) {
            $invoice = $invoices->first();
            $invoice->setInvoiceDate(new \DateTime());
            $invoice->setMedicalRecord($medicalRecord);
            $invoice->setTotalPaid($medicalRecord->getTotalPaid());
            $invoice->setTotalAmount($medicalRecord->getAgreedAmout());
            $invoice->setRemainingDue($medicalRecord->getRemainingDue());
            $invoice->setInvoiceNumber($invoice->generateInvoiceNumber());
            $visits = $medicalRecord->getVisits();
        
            foreach ($visits as $visit) {
                foreach ($visit->getPayments() as $payment) {
                    // Clone or use directly — assuming you want the same payment entity linked to invoice
                    $invoice->addPayment($payment);
                }
            }

            $this->entityManager->persist($invoice);
            $this->entityManager->flush();
            $invoice->setMedicalRecord($medicalRecord);
            $medicalRecord->addInvoice($invoice); // ✅ keep the collection in sync
        } else {
            $invoice = new InvoiceOrmEntity();
            $invoice->setInvoiceDate(new \DateTime());
            $invoice->setMedicalRecord($medicalRecord);
            $invoice->setTotalPaid($medicalRecord->getTotalPaid());
            $invoice->setTotalAmount($medicalRecord->getAgreedAmout());
            $invoice->setRemainingDue($medicalRecord->getRemainingDue());
            $invoice->setInvoiceNumber($invoice->generateInvoiceNumber());
            $visits = $medicalRecord->getVisits();
        
            foreach ($visits as $visit) {
                foreach ($visit->getPayments() as $payment) {
                    // Clone or use directly — assuming you want the same payment entity linked to invoice
                    $invoice->addPayment($payment);
                }
            }

            $this->entityManager->persist($invoice);
            $this->entityManager->flush();
            $invoice->setMedicalRecord($medicalRecord);
            $medicalRecord->addInvoice($invoice); // ✅ keep the collection in sync
        }
    

        
    }
}