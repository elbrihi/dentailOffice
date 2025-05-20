<?php

namespace DentalOffice\MedicalRecordBundle\Infrastructure\Persistence\Doctrine\Processor\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use DateTimeImmutable;
use DentalOffice\MedicalRecordBundle\Domain\Entity\MedicalRecord;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Clock\ClockInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class MedicalRecordPutProcessor implements ProcessorInterface
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
        
    )
    {
        
    }
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): MedicalRecord
    {
        /** @var Request $request */
        $request = $context['request'];
    
        $medicalRecordData = json_decode($request->getContent(), true);
    
        $existingRecord = $this->entityManager->getRepository(MedicalRecord::class)->find($uriVariables['id']);
    
        if (!$existingRecord instanceof MedicalRecord) {
            throw new \RuntimeException("MedicalRecord not found.");
        }
    
        try {
            $visitDate = new DateTimeImmutable($medicalRecordData["visit_date"]);
            $followUpDate = new DateTimeImmutable($medicalRecordData["follow_up_date"]);
        } catch (\Exception $e) {
            throw new BadRequestHttpException("Invalid date format, expected YYYY-MM-DD.");
        }

        $agreededAmount = $medicalRecordData["agreedAmout"];
        $totalPaid = 0;
        $remainingDue = $agreededAmount - $totalPaid;
    
        $data->setVisitDate($visitDate);
        $data->setChiefComplaint($medicalRecordData["chief_complaint"]);
        $data->setClinicalDiagnosis($medicalRecordData["clinical_diagnosis"]);
        $data->setTreatmentPlan($medicalRecordData["treatment_plan"]);
        $data->setPrescriptions($medicalRecordData["prescriptions"]);
        $data->setNotes($medicalRecordData["notes"]);
        $data->setFollowUpDate($followUpDate);
        $data->setCreatedAt($existingRecord->getCreatedAt());
        $data->setCreatedBy($existingRecord->getCreatedBy());
        $data->setModifiedAt($this->clock->now());
        $data->setModifiedBy($this->security->getUser());
        $data->setPatient($existingRecord->getPatient());
        $data->setAgreedAmout($agreededAmount);
        $data->setTotalPaid($totalPaid);
        $data->setRemainingDue($remainingDue);
    
        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
    
}
