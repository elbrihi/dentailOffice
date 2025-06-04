<?php

namespace DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Processor\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use DateTimeImmutable;
use DentalOffice\AppointmentSchedulingBundle\Application\Event\VisitCreatedEvent;
use DentalOffice\AppointmentSchedulingBundle\Domain\Entity\Visit;
use DentalOffice\InvoiceBundle\Application\Event\InvoiceCreatedEvent;
use DentalOffice\InvoiceBundle\Application\Event\InvoiceUpdatedEvent;
use DentalOffice\InvoiceBundle\Domain\Entity\Invoice;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Clock\ClockInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class VisitPutStateProcessor implements ProcessorInterface
{
                // providers
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor,
        #[Autowire(service: 'api_platform.doctrine.orm.state.remove_processor')]
        private ProcessorInterface $removeProcessor,
        private Security $security, 
        private EntityManagerInterface $entityManager,
        private ClockInterface $clock,
        private EventDispatcherInterface $dispatcher
        
    )
    {      
    }
   
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Visit
    {


        $visitId = $uriVariables["id"];

  
        $visitEntity = $this->entityManager->getRepository(Visit::class)->findOneBy([
            'id' => $visitId
        ]);

        
        if (!$visitEntity) {
            throw new NotFoundHttpException("Visit not found.");
        }

        $paymentEntity = $visitEntity->getPayments()[0];

    
        $createdAt = $visitEntity->getCreatedAt();
        $createdBy = $visitEntity->getCreatedBy();
        $medicalRecord = $visitEntity->getMedicalRecord();
        $medicalRecordId = $visitEntity->getMedicalRecord()->getId();
       

       
        $request = $context["request"];
        $user = $this->security->getUser();

        // ✅ Keep visitData separate from the Visit object
        $visitData = json_decode($request->getContent(), true);

        try {
            $paymentDate=$visitDate = new DateTimeImmutable($visitData["visit_date"]);
        } catch (\Exception $e) {
            throw new BadRequestHttpException("Invalid birthDate format, expected YYYY-MM-DD.");
        }

        /** @var Visit $visit */
        $visit = $data; // This is your Visit object passed in from the caller

        $visiAmout=$visitData["amount_paid"];
 
        $paymentEntity->setMethod($visitData["payments"][0]["method"]);
        $paymentEntity->setPaymentDate($paymentDate);
        $paymentEntity->setAmount($visiAmout);
        $visitEntity->setVisitDate( $visitDate );
        $visitEntity->setNotes($visitData["notes"]);
        $visitEntity->setAmountPaid($visiAmout);
        $visitEntity->setDurationMinutes($visitData["duration_minutes"]);
        $visitEntity->setRemainingDueAfterVisit($visitData["remaining_due_after_visit"]);
        $visitEntity->setMedicalRecord($medicalRecord);
        $medicalRecord->getVisits()->add($visitEntity);

        
        $visitEntity->setModifiedAt($this->clock->now());
        $visitEntity->setModifiedBy($user);
        $visitEntity->setCreatedAt($createdAt);
        $visitEntity->setCreatedBy( $createdBy );
        $visitEntity->addPayment($paymentEntity);

        // No more setVisitDate here again
       
        // Handle the state...

        $visit = $this->persistProcessor->process($visitEntity, $operation, $uriVariables, $context);

        $event = new VisitCreatedEvent($visit,$medicalRecordId);
        
        $this->dispatcher->dispatch($event, VisitCreatedEvent::class);

        $invoiceEvent = new InvoiceUpdatedEvent($medicalRecordId);
        $this->dispatcher->dispatch($invoiceEvent, InvoiceUpdatedEvent::class);
        return $visit;
    }

}
