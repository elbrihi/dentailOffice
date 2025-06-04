<?php

namespace DentalOffice\InvoiceBundle\Application\UseCase;

use DentalOffice\InvoiceBundle\Application\Event\InvoiceCreatedEvent;
use DentalOffice\InvoiceBundle\Domain\Entity\Invoice;
use DentalOffice\MedicalRecordBundle\Domain\Entity\MedicalRecord;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: InvoiceCreatedEvent::class)]
class CreatingInvoiceOnMedicalRecord
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    )
    {
      
    }

    public function __invoke(InvoiceCreatedEvent $event){
       
        
    }
    
}

