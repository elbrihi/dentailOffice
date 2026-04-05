<?php

namespace DentalOffice\InvoiceBundle\Tests\Application\Subscriber;

use DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Entity\AppointmentOrmEntity;
use DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Entity\VisitOrmEntity;
use DentalOffice\InvoiceBundle\Infrastructure\Persistence\Doctrine\Entity\InvoiceItemOrmEntity;
use DentalOffice\InvoiceBundle\Infrastructure\Persistence\Doctrine\Entity\InvoiceOrmEntity;
use DentalOffice\MedicalRecordBundle\Infrastructure\Persistence\Doctrine\Entity\MedicalRecordOrmEntity;
use DentalOffice\MedicalRecordBundle\Infrastructure\Persistence\Doctrine\Entity\PrescriptionOrmEntity;
use DentalOffice\PatientBundle\Domain\Entity\Patient;
use DentalOffice\UserBundle\Domain\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Clock\ClockInterface;

class InvoiceTest extends KernelTestCase
{
   
    protected EntityManagerInterface $entityManager;
    protected ClockInterface $clock;
    protected const CNI = 'CNI987654';
    protected $container;

    public static string $username = "testuser";
    protected function setUp():void
    {


        self::bootKernel();

        $container = static::getContainer();
        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->clock = $container->get(ClockInterface::class); // 👈 Fix here


        foreach ($this->entityManager->getRepository(InvoiceItemOrmEntity::class)->findAll() as $invoice) {

            
            $this->entityManager->remove($invoice);
        }

       
        foreach ($this->entityManager->getRepository(InvoiceOrmEntity::class)->findAll() as $invoice) {
            $this->entityManager->remove($invoice);
        }
       
        // 1. Prescreption (deepest child)
        foreach ($this->entityManager->getRepository(PrescriptionOrmEntity::class)->findAll() as $presciption) {
            $this->entityManager->remove( $presciption);
        }

        // 1. Visits (deepest child)
        foreach ($this->entityManager->getRepository(VisitOrmEntity::class)->findAll() as $visit) {
            $this->entityManager->remove($visit);
        }

        // 2. Medical Records
        foreach ($this->entityManager->getRepository(MedicalRecordOrmEntity::class)->findAll() as $mr) {
            $this->entityManager->remove($mr);
        }

        // 3. Appointments
        foreach ($this->entityManager->getRepository(AppointmentOrmEntity::class)->findAll() as $appointment) {
            $this->entityManager->remove($appointment);
        }

        // 4. Patients
        foreach ($this->entityManager->getRepository(Patient::class)->findAll() as $patient) {
            $this->entityManager->remove($patient);
        }

        // 5. Users (root)
        foreach ($this->entityManager->getRepository(User::class)->findAll() as $user) {
            $this->entityManager->remove($user);
        }

        $this->entityManager->flush();

    }

}