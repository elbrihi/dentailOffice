<?php

namespace DentalOffice\PatientBundle\Infrastructure\Persistence\Doctrine\Processor\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use DateTimeImmutable;
use DentalOffice\PatientBundle\Domain\Entity\Patient;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Clock\ClockInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class PatientPutProcessor implements ProcessorInterface
{
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
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Patient
    {
       
        $patient = json_decode($context['request']->getContent(), true);

        $request = $context['request'];
       
        $patient = json_decode($request->getContent(), true);

        $patienFromDataBase = $this->entityManager->getRepository(Patient::class)->findOneBy(
            ['id' => $uriVariables['id']]
        );

        try {
            $birthDate = new DateTimeImmutable($patient["birthDate"]);
        } catch (\Exception $e) {
            throw new BadRequestHttpException("Invalid birthDate format, expected YYYY-MM-DD.");
        }
    
        $data->setLastName($patient["lastName"]);
        $data->setFirstName($patient["firstName"]);
        $data->setBirthDate($birthDate);
        $data->setGender($patient["gender"]);
        $data->setCni($patient["cni"]);
        $data->setPhone($patient["phone"]);
        $data->setEmail($patient["email"]);
        $data->setAddress($patient["address"]);
        $data->setBloodType($patient["bloodType"]);
        $data->setMedicalHistory($patient["medicalHistory"]);
        $data->setNotes($patient["notes"]);
        $data->setCreatedAt($this->clock->now());
        $data->setCreatedBy($patienFromDataBase->getCreatedBy());
        $data->setModifiedAt($patienFromDataBase->getCreatedAt());
        $data->setModifiedBy($this->security->getUser());
        $data->setStatus($patient["status"]);

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
        
         
    }
}
