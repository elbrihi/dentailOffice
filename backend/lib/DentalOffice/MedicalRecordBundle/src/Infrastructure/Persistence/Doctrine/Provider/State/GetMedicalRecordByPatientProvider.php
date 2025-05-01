<?php

namespace DentalOffice\MedicalRecordBundle\Infrastructure\Persistence\Doctrine\Provider\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use DentalOffice\MedicalRecordBundle\Domain\Repository\MedicalRecordRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException as ExceptionNotFoundHttpException;

class GetMedicalRecordByPatientProvider implements ProviderInterface
{

    public function __construct(private MedicalRecordRepository $medicalRecordRepository)
    {
        $this->medicalRecordRepository = $medicalRecordRepository;
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): iterable
    {
        $patientId = $uriVariables['patientId'] ?? null;

        if (!$patientId) {
            throw new ExceptionNotFoundHttpException ('Patient ID is required.');
        }

        return $this->medicalRecordRepository->findBy(['patient' => $patientId], ['id' => 'DESC']);
    }
}



