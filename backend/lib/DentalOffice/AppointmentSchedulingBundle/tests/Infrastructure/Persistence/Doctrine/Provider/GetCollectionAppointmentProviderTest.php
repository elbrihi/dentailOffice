<?php

namespace DentalOffice\AppointmentSchedulingBundle\Tests\Functional\Domail\Entity;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use DateTimeImmutable;
use DentalOffice\AppointmentSchedulingBundle\Domain\Entity\Appointment;
use DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Provider\State\AppointmentsGetCollectionProvider;
use DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Provider\State\AppointmentStateProvider;
use DentalOffice\PatientBundle\Domain\Entity\Patient;
use DentalOffice\UserBundle\Domain\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class GetCollectionAppointmentProviderTest extends ApiTestCase
{
    private EntityManagerInterface $entityManager;
    private ClockInterface $clock;
    private AppointmentsGetCollectionProvider $appointmentsProvider;
    
    protected function setUp():void
    {
        $container = static::getContainer();
        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->appointmentsProvider = $container->get('DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Provider\State\AppointmentsGetCollectionProvider');
        $this->clock = $container->get(ClockInterface::class); // 👈 Fix here
        


        $this->entityManager->createQuery('DELETE FROM DentalOffice\AppointmentSchedulingBundle\Domain\Entity\Appointment')->execute();
        $this->entityManager->createQuery('DELETE FROM DentalOffice\PatientBundle\Domain\Entity\Patient')->execute();
        $this->entityManager->createQuery('DELETE FROM DentalOffice\UserBundle\Domain\Entity\User')->execute();
    }

    public function testGetCollectionAppointment()
    {


        $operation = new GetCollection(); 
      
        $this->savaData();

        $context["filters"]["page"] = 1;
        $context["filters"]["itemsPerPage"] = 3;

        $result = $this->appointmentsProvider->provide($operation,[],$context);
        
        $appointments = iterator_to_array($result);

        
        $this->assertCount(3, $appointments); // assert pagination
        $this->assertInstanceOf(Appointment::class, $appointments[0]); // type check
        $this->assertTrue(true);

    }


    private function  savaData()
    {
        $em = $this->getEntityManager();
        // entering user 
        $client = static::createClient();

        // Create user and persist to DB
        $user = new User();
        $user->setUsername('admin');
        $user->setPassword('hashed_password'); // Mock this or hash properly
        $user->setRoles(['ROLE_ADMIN']);
        $user->setApiToken('sample_token_value');
        
        $em->persist($user);

        $tokenStorage=static::getContainer()->get('security.token_storage');

        $tokenStorage->setToken(new UsernamePasswordToken(
            $user,
            'admin',
            $user->getRoles()
        ));
        
        // patient 
        $patient =  new Patient();
        $birthDate = new DateTimeImmutable("1985-06-15");
        $patient->setLastName("Doe");
        $patient->setFirstName("Jane");
        $patient->setBirthDate($birthDate);
        $patient->setGender("Female");
        $patient->setCni("CNI987654");
        $patient->setPhone("123456789");
        $patient->setEmail("jane.doe@example.com");
        $patient->setAddress("42 Sunset Blvd");
        $patient->setBloodType("O+");
        $patient->setMedicalHistory("Asthma");
        $patient->setNotes("Test patient");
        $patient->setCreatedAt($this->clock->now());
        

        if (!$user instanceof \DentalOffice\UserBundle\Domain\Entity\User) {
            throw new \LogicException('Authenticated user must be an instance of DentalOffice\UserBundle\Domain\Entity\User.');
        }
        $patient->setCreatedBy($user);
        $patient->setModifiedAt($this->clock->now());
        $patient->setModifiedBy($user);
        $patient->setStatus(true);
        
        
        $em->persist($patient);
        // entering appointment 

        $appointmentDate =  new DateTimeImmutable("2024-03-13T00:00:00+01:00");
      

        for($i=0; $i< 10 ; $i++)
        {
            $appointment = new Appointment();
            $appointment->setReason("Jane update _".$i);
            $appointment->setAppointmentDate($appointmentDate);
            $appointment->setModifiedAt($this->clock->now());
            $appointment->setCreatedAt($this->clock->now());
            $appointment->setCreatedBy($user );
            $appointment->setModifiedBy($user );
            $appointment->setUser($user);
            $appointment->setStatus(true);
            $appointment->setPatient($patient);
    
            $em->persist($appointment);
        }


        $this->entityManager->flush();
    }

    private function getEntityManager()
    {
        return self::getContainer()->get('doctrine')->getManager();
    }

}