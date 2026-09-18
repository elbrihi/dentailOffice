<?php

namespace DentalOffice\MedicalRecordBundle\Infrastructure\Persistence\Doctrine\Repository;

use DentalOffice\MedicalRecordBundle\Infrastructure\Persistence\Doctrine\Entity\StepOrmEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StepOrmEntity>
 *
 * @method StepOrmEntity|null find($id, $lockMode = null, $lockVersion = null)
 * @method StepOrmEntity|null findOneBy(array $criteria, array $orderBy = null)
 * @method StepOrmEntity[]    findAll()
 * @method StepOrmEntity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StepOrmEntityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StepOrmEntity::class);
    }

//    /**
//     * @return StepOrmEntity[] Returns an array of StepOrmEntity objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?StepOrmEntity
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
