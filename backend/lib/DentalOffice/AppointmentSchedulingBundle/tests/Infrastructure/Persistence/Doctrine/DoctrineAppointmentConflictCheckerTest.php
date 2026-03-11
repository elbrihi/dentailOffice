<?php


namespace DentalOffice\AppointmentSchedulingBundle\Tests\Infrastructure\Persistence\Doctrinel;

use DateTimeImmutable;
use DentalOffice\AppointmentSchedulingBundle\Domain\Exception\AppointmentConflictException;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\PractitionerId;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\TimeSlot;
use DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\DoctrineAppointmentConflictChecker;
use DentalOffice\AppointmentSchedulingBundle\Tests\Infrastructure\Persistence\Doctrine\Processor\State\AppointmentProcessorTest;
use DentalOffice\UserBundle\Domain\Entity\User;
use PHPUnit\Framework\TestCase;

class DoctrineAppointmentConflictCheckerTest extends AppointmentProcessorTest
{
    public function test_it_detects_conflict(): void
    {
        // ------------------------------------
        // Arrange
        // ------------------------------------

        $this->savePatient();
        $this->saveAppointment(); // 09:00 → 09:30 existing appointment
        
   
        // 3. Clear the EM to force reload from DB
        $this->entityManager->clear();
        $checker = new DoctrineAppointmentConflictChecker($this->entityManager);

        $user = $this->entityManager->getRepository(User::class)
                ->findAll([
                  
                ]);
        $practitionerId = PractitionerId::fromInt($user[0]->getId());

        $timeSlot = new TimeSlot(
            new DateTimeImmutable('2026-03-01 09:10:00'),
            new DateTimeImmutable('2026-03-01 09:20:00')
        );

     //   $this->expectException(AppointmentConflictException::class);

        $hasConflict = $checker->hasConflict($practitionerId, $timeSlot);

       
        $this->assertTrue($hasConflict);
    }

    public function test_it_detects_not_conflict(): void
    {
        // ------------------------------------
        // Arrange
        // ------------------------------------

        $this->savePatient();
        $this->saveAppointment(); // 09:00 → 09:30 existing appointment
        
   
        // 3. Clear the EM to force reload from DB
        $this->entityManager->clear();
        $checker = new DoctrineAppointmentConflictChecker($this->entityManager);

        $user = $this->entityManager->getRepository(User::class)
                ->findAll([
                  
                ]);
        $practitionerId = PractitionerId::fromInt($user[0]->getId());

        $timeSlot = new TimeSlot(
            new DateTimeImmutable('2026-03-01 10:00:00'),
            new DateTimeImmutable('2026-03-01 10:30:00')
        );

     //   $this->expectException(AppointmentConflictException::class);

        $hasConflict = $checker->hasConflict($practitionerId, $timeSlot);

        $this->assertFalse($hasConflict);
    }
}