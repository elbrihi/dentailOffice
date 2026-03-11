<?php

namespace DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Repository;

use DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Entity\Testo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Testo>
 *
 * @method Testo|null find($id, $lockMode = null, $lockVersion = null)
 * @method Testo|null findOneBy(array $criteria, array $orderBy = null)
 * @method Testo[]    findAll()
 * @method Testo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TestoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Testo::class);
    }

//    /**
//     * @return Testo[] Returns an array of Testo objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Testo
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
