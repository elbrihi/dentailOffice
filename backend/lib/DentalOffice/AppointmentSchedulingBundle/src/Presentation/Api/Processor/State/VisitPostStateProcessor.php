<?php

namespace DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Processor\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use DateTimeImmutable;
use DentalOffice\AppointmentSchedulingBundle\Application\Event\VisitCreatedEvent;
use DentalOffice\AppointmentSchedulingBundle\Domain\Entity\Visit;
use DentalOffice\InvoiceBundle\Application\Event\InvoiceCreatedEvent;
use DentalOffice\InvoiceBundle\Domain\Entity\Invoice;
use DentalOffice\MedicalRecordBundle\Domain\Entity\MedicalRecord;
use DentalOffice\PaymentsBundle\Domain\Entity\Payment;
use DentalOffice\PaymentsBundle\Domain\Repository\PaymentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Clock\ClockInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
                            ->findOneBy(['id' => $medicalRecordId]);

        if (!$medicalRecord) {
            throw new NotFoundHttpException('MedicalRecord not found.');
        }

        $request = $context["request"];
        $body = json_decode($request->getContent(), true);

        $visitDate = new \DateTimeImmutable($body["visit_date"]);
        $paymentDate = new \DateTimeImmutable($body["payments"][0]["payment_date"]);
        $amountPaid = $body["amount_paid"];

        // 1️⃣ Create related Payment
        $payment = new Payment();
        $payment->setAmount($amountPaid);
        $payment->setPaymentDate($paymentDate);
        $payment->setMethod($body["payments"][0]["method"]);

        $this->entityManager->persist($payment);
        $this->entityManager->flush();

        // 2️⃣ Create the real Visit Entity from DTO data
        $visit = new Visit();

        $visit->setDurationMinutes($body["duration_minutes"]);
        $visit->setType($body["type"]);
        $visit->setVisitDate($visitDate);
        $visit->setNotes($body["notes"]);
        $visit->setAmountPaid($amountPaid);
        $visit->setRemainingDueAfterVisit($body["remaining_due_after_visit"]);
        $visit->setStatus(true);
        $visit->setMedicalRecord($medicalRecord);
        $visit->addPayment($payment);
        $visit->setCreatedAt($this->clock->now());
        $visit->setModifiedAt($this->clock->now());

        $user = $this->security->getUser();
        $visit->setCreatedBy($user);
        $visit->setModifiedBy($user);
        $visit->setMedicalRecord($medicalRecord);
        $medicalRecord->addVisit($visit);

        
        // 3️⃣ Persist using the default persist processor
        $visit =  $this->persistProcessor->process($visit, $operation, $uriVariables, $context);    
            
        $event = new VisitCreatedEvent($visit,$medicalRecordId);

        // visit ===> invoice
        // dispatche invoice  update invoice 
        $medicalRecord = $this->dispatcher->dispatch($event, VisitCreatedEvent::class);

        $invoiceEvent = new InvoiceCreatedEvent($medicalRecordId);
        $this->dispatcher->dispatch($invoiceEvent, InvoiceCreatedEvent::class);
            
        return $visit;

        
    }
}
