<?php

namespace DentalOffice\InvoiceBundle\Application\UseCase;

use DentalOffice\InvoiceBundle\Application\Event\InvoiceCreatedEvent;
use DentalOffice\InvoiceBundle\Domain\Entity\Invoice;
use DentalOffice\MedicalRecordBundle\Domain\Entity\MedicalRecord;
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
        $medicalRecord = $this->entityManager->getRepository(MedicalRecord::class)
            ->find($medicalRecordId);

        if (!$medicalRecord) {
            throw new \Exception("MedicalRecord not found");
        }

        $invoice = new Invoice();
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
        
    }
}