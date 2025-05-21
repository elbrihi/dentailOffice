<?php

namespace DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Processor\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use DateTimeImmutable;
use DentalOffice\AppointmentSchedulingBundle\Application\Event\VisitCreatedEvent;
use DentalOffice\AppointmentSchedulingBundle\Domain\Entity\Visit;
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
        $visitId = $uriVariables["visitId"];

        $visitEntity = $this->entityManager->getRepository(Visit::class)->findOneBy([
            'id' => $visitId
        ]);

        if (!$visitEntity) {
            throw new NotFoundHttpException("Visit not found.");
        }


        $createdAt = $visitEntity->getCreatedAt();
        $createdBy = $visitEntity->getCreatedBy();
        $medicalRecord = $visitEntity->getMedicalRecord();

        $request = $context["request"];
        $user = $this->security->getUser();

        // ✅ Keep visitData separate from the Visit object
        $visitData = json_decode($request->getContent(), true);

        try {
            $visitDate = new DateTimeImmutable($visitData["visit_date"]);
        } catch (\Exception $e) {
            throw new BadRequestHttpException("Invalid birthDate format, expected YYYY-MM-DD.");
        }

        /** @var Visit $visit */
        $visit = $data; // This is your Visit object passed in from the caller

        $visitEntity->setVisitDate( $visitDate );
        $visitEntity->setNotes($visitData["notes"]);
        $visitEntity->setAmountPaid($visitData["amount_paid"]);
        $visitEntity->setRemainingDueAfterVisit($visitData["remaining_due_after_visit"]);
        $visitEntity->setMedicalRecord($medicalRecord);
        $medicalRecord->getVisits()->add($visitEntity);
        $visitEntity->setModifiedAt($this->clock->now());
        $visitEntity->setModifiedBy($user);
        $visitEntity->setCreatedAt($createdAt);
        $visitEntity->setCreatedBy( $createdBy );
        // No more setVisitDate here again
       
        // Handle the state...

        $visit = $this->persistProcessor->process($visitEntity, $operation, $uriVariables, $context);

        $event = new VisitCreatedEvent($visit);
        
        $this->dispatcher->dispatch($event, VisitCreatedEvent::class);

        return $visit;
    }

}
