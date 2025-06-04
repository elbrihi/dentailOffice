<?php

namespace DentalOffice\AppointmentSchedulingBundle\Application\UseCase;

use DentalOffice\AppointmentSchedulingBundle\Application\Event\VisitCreatedEvent;
use DentalOffice\MedicalRecordBundle\Domain\Entity\MedicalRecord;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: VisitCreatedEvent::class)]
class UpdateMedicalRecordOnVisit
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    )
    {}

    public function __invoke(VisitCreatedEvent $event): void
    {

        // remaining_due - 

        $medicalRecordId = $event->getMedicalRecordId();
        
      
        $medicalRecord = $this->entityManager->getRepository(MedicalRecord::class)->findOneBy([
              'id' => $medicalRecordId
            ]
        );
        
 
        $agreedAmout = $medicalRecord->getAgreedAmout();

        $totalPaid = 0;

        $remainingDue = $agreedAmout;
        
        // dd($totalPaid, $remainingDue);

        $visits = $medicalRecord ->getVisits(); // collections 

        $visits = $medicalRecord->getVisits()->toArray();


        $amountPaidVisits = 0;

        foreach ($visits as $visit) 
        {
            $amountPaidVisits = $amountPaidVisits + $visit->getAmountPaid();
        
        }
        
        $totalPaid = $totalPaid + $amountPaidVisits ;
        $remainingDue  = $remainingDue - $amountPaidVisits ;

        $medicalRecord->setTotalPaid( $totalPaid);
        $medicalRecord->setRemainingDue($remainingDue);
       
        $this->entityManager->persist($medicalRecord);
        $this->entityManager->flush();
   
       // dd($visits);
    }
}