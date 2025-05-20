<?php

namespace DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Processor\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use DateTimeImmutable;
use DentalOffice\AppointmentSchedulingBundle\Domain\Entity\Appointment;
use DentalOffice\PatientBundle\Domain\Entity\Patient;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Clock\ClockInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class AppointmentStateProcessor implements ProcessorInterface
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
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Appointment
    {

        $request = $context["request"];

        $user = $this->security->getUser();
        
        $appointment =  json_decode($request->getContent(), true);

         
        try {
            $appointmentDate =  new DateTimeImmutable($appointment["appointment_date"]);
        } catch (\Exception $e) {
            throw new BadRequestHttpException("Invalid birthDate format, expected YYYY-MM-DD.");
        }
       
        $patient = $this->entityManager->getRepository(Patient::class)->findOneBy([
            'id' => $uriVariables['patientId']
                
        ]);


        
        $data->setReason($appointment["reason"]);
        $data->setAppointmentDate($appointmentDate);
        $data->setModifiedAt($this->clock->now());
        $data->setCreatedAt($this->clock->now());
        $data->setCreatedBy($user );
        $data->setModifiedBy($user );
        $data->setUser($user);
        $data->setStatus(true);
        $data->setPatient($patient);

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
        

    }
}
