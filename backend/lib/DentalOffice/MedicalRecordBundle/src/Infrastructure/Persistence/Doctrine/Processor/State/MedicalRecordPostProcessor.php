<?php

namespace DentalOffice\MedicalRecordBundle\Infrastructure\Persistence\Doctrine\Processor\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use DateTimeImmutable;
use DentalOffice\AppointmentSchedulingBundle\Domain\Entity\Appointment;
use DentalOffice\InvoiceBundle\Application\Event\InvoiceCreatedEvent;
use DentalOffice\MedicalRecordBundle\Domain\Entity\MedicalRecord;
use DentalOffice\PatientBundle\Domain\Entity\Patient;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Clock\ClockInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class MedicalRecordPostProcessor implements ProcessorInterface
{
    // providers
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor,
        #[Autowire(service: 'api_platform.doctrine.orm.state.remove_processor')]
        private ProcessorInterface $removeProcessor,
        private Security $security, 
        private EntityManagerInterface $entityManager ,
        private ClockInterface $clock,
        private EventDispatcherInterface $dispatcher
        
    )
    {
        
    }
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): MedicalRecord
    {
              
        
        $medicalRecord = json_decode($context['request']->getContent(),true);
        /** @var Request $request */
        $request = $context['request'];

        
        $patientId = $uriVariables["patientId"];
        $appointmentId = $uriVariables["appointmentId"];

  
        $patient = $this->entityManager->getRepository(Patient::class)
                   ->findById([
                        'id' =>  $patientId 
        ]);

        $appointment = $this->entityManager->getRepository(Appointment::class)->findOneBy([
            'id' => $appointmentId
        ]);

        
        try {
            $visitDate = new DateTimeImmutable($medicalRecord["visit_date"]);
        } catch (\Exception $e) {
            throw new BadRequestHttpException("Invalid birthDate format, expected YYYY-MM-DD.");
        }
        try {
            $followUpDate = new DateTimeImmutable($medicalRecord["follow_up_date"]);
        } catch (\Exception $e) {
            throw new BadRequestHttpException("Invalid birthDate format, expected YYYY-MM-DD.");
        }

        $agreededAmount = $medicalRecord["agreedAmout"];
        $totalPaid = 0;
        $remainingDue = $agreededAmount - $totalPaid;

        $data->setVisitDate($visitDate);
     
        $data->setChiefComplaint($medicalRecord["chief_complaint"]);
 

        $data->setClinicalDiagnosis($medicalRecord["clinical_diagnosis"]);
      
        $data->setTreatmentPlan($medicalRecord["treatment_plan"]);
     
        $data->setPrescriptions($medicalRecord["prescriptions"]);     
      
        $data->setNotes($medicalRecord["follow_up_date"]);

        $data->setFollowUpDate($followUpDate);
        
        $data->setPatient($patient[0]);
 
        $data->setCreatedAt($this->clock->now());
        $data->setCreatedBy($this->security->getUser());
        $data->setModifiedAt($this->clock->now());
        $data->setModifiedBy($this->security->getUser());

  
        $data->setAgreedAmout($agreededAmount);
        $data->setTotalPaid($totalPaid);
        $data->setRemainingDue($remainingDue);
        $data->setAppointment($appointment);
        
        $medicalRecord = $this->persistProcessor->process($data, $operation, $uriVariables, $context);

        $this->dispatcher->dispatch(new InvoiceCreatedEvent($medicalRecord->getId()));
        return  $medicalRecord;

        
        
    }
}
