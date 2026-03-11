<?php

namespace DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine;

use DentalOffice\AppointmentSchedulingBundle\Domain\Exception\AppointmentConflictException;
use DentalOffice\AppointmentSchedulingBundle\Domain\Service\AppointmentConflictChecker;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\PractitionerId;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\TimeSlot;
use DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Entity\AppointmentOrmEntity;
use Doctrine\ORM\EntityManagerInterface;

class DoctrineAppointmentConflictChecker implements AppointmentConflictChecker
{
        public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function hasConflict(
        PractitionerId $practitionerId,
        TimeSlot $timeSlot
    ): bool {
                $qb = $this->entityManager->createQueryBuilder();

                $qb
                    ->select('1')
                    ->from(AppointmentOrmEntity::class, 'a')
                    ->where('a.user = :practitioner')
                    ->andWhere('a.start < :end')
                    ->andWhere('a.end > :start')
                    ->andWhere('a.status != :cancelled')
                    ->setParameter('practitioner', $practitionerId->generate())
                    ->setParameter('start', $timeSlot->getStart())
                    ->setParameter('end', $timeSlot->getEnd())
                    ->setParameter('cancelled', 'cancelled')
                    ->setMaxResults(1);

                return $qb
                    ->getQuery()
                    ->getOneOrNullResult() !== null;
    }

        /**
     * Throws exception if conflict detected
     */
    public function assertNoConflict(PractitionerId $practitionerId, TimeSlot $timeSlot): void
    {
        if ($this->hasConflict($practitionerId, $timeSlot)) {
            throw AppointmentConflictException::forTimeSlot($practitionerId, $timeSlot);
        }
    }
}