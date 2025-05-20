<?php

namespace DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Processor\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use DateTimeImmutable;
use DentalOffice\AppointmentSchedulingBundle\Application\Event\VisitCreatedEvent;
use DentalOffice\AppointmentSchedulingBundle\Domain\Entity\Visit;
use DentalOffice\MedicalRecordBundle\Domain\Entity\MedicalRecord;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Clock\ClockInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class VisitPostStateProcessor implements ProcessorInterface
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
  
        $medicalRecordId = $uriVariables['medicalRecordId'];
        $medicalRecord = $this->entityManager->getRepository(MedicalRecord::class)
                            ->findOneBy([
                                'id' =>  $medicalRecordId
                        ]);
        $request = $context["request"];

        $user = $this->security->getUser();
        $visit =  json_decode($request->getContent(), true);
        try {
            $visitDate =  new DateTimeImmutable($visit["visit_date"]);
        } catch (\Exception $e) {
            throw new BadRequestHttpException("Invalid birthDate format, expected YYYY-MM-DD.");
        }
        $data->setVisitDate($visitDate );
        $data->setNotes($visit["notes"]);
        $data->setAmountPaid($visit["amount_paid"]);
        $data->setRemainingDueAfterVisit($visit["remaining_due_after_visit"]);
        $data->setMedicalRecord($medicalRecord);
        $medicalRecord->getVisits()->add($data);
        $data->setCreatedAt($this->clock->now());
        $data->setModifiedAt($this->clock->now());
        $data->setCreatedBy($user);
        $data->setModifiedBy($user);
        $data->setVisitDate($visitDate);
        // Handle the state

        $visit = $this->persistProcessor->process($data, $operation, $uriVariables, $context);

      
            $event = new VisitCreatedEvent($visit);
            $this->dispatcher->dispatch($event, VisitCreatedEvent::class);

        return $visit;

        
    }
}
