<?php

namespace DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Processor\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use DateTimeImmutable;
use DentalOffice\AppointmentSchedulingBundle\Application\Event\VisitCreatedEvent;
use DentalOffice\AppointmentSchedulingBundle\Domain\Entity\Visit;
use DentalOffice\InvoiceBundle\Application\Event\InvoiceCreatedEvent;
use DentalOffice\MedicalRecordBundle\Domain\Entity\MedicalRecord;
use DentalOffice\PaymentsBundle\Domain\Entity\Payment;
use DentalOffice\PaymentsBundle\Domain\Repository\PaymentRepository;
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
        private EventDispatcherInterface $dispatcher,
        private PaymentRepository $paymentRepository
        
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
        $visiAmout = $visit["amount_paid"];
         ;
      
        try {
            $visitDate =  new DateTimeImmutable($visit["visit_date"]);
        } catch (\Exception $e) {
            throw new BadRequestHttpException("Invalid birthDate format, expected YYYY-MM-DD.");
        }

       
        try {
            $paymentDate =  new DateTimeImmutable( $visit["payments"][0]["payment_date"]);
        } catch (\Exception $e) {
            throw new BadRequestHttpException("Invalid birthDate format, expected YYYY-MM-DD.");
        }

       

        $payment = new Payment();
        $payment->setAmount( $visiAmout);
        $payment->setPaymentDate($paymentDate);
        $payment->setMethod($visit["payments"][0]["method"]);
        $payment->setPaymentDate($paymentDate );

        $this->entityManager->persist($payment);
        $this->entityManager->flush();

        $payment = $this->paymentRepository->findLastPayment();


        $data->setDurationMinutes( $visit["duration_minutes"]);
        $data->setType($visit["type"]);
        $data->setVisitDate($visitDate );
        $data->setNotes($visit["notes"]);
        $data->setAmountPaid($visiAmout);
        $data->setRemainingDueAfterVisit($visit["remaining_due_after_visit"]);
        $data->setStatus(true);
        $data->setMedicalRecord($medicalRecord);
        $medicalRecord->getVisits()->add($data);
        $data->setCreatedAt($this->clock->now());
        $data->setModifiedAt($this->clock->now());
        $data->setCreatedBy($user);
        $data->setModifiedBy($user);
        $data->setVisitDate($visitDate);
        $data->addPayment( $payment);
        // Handle the state

        
        
        $visit = $this->persistProcessor->process($data, $operation, $uriVariables, $context);

      
        $event = new VisitCreatedEvent($visit,$medicalRecordId);
        // visit ===> invoice
        // dispatche invoice  update invoice 
        $medicalRecord = $this->dispatcher->dispatch($event, VisitCreatedEvent::class);

        $invoiceEvent = new InvoiceCreatedEvent($medicalRecordId);
        $this->dispatcher->dispatch($invoiceEvent, InvoiceCreatedEvent::class);
      
        return $visit;

        
    }
}
