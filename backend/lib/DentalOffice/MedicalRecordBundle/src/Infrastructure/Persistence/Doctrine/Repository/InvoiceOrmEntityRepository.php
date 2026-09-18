<?php

namespace DentalOffice\MedicalRecordBundle\Infrastructure\Persistence\Doctrine\Repository;

use DentalOffice\MedicalRecordBundle\Infrastructure\Persistence\Doctrine\Entity\InvoiceOrmEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InvoiceOrmEntity>
 *
 * @method InvoiceOrmEntity|null find($id, $lockMode = null, $lockVersion = null)
 * @method InvoiceOrmEntity|null findOneBy(array $criteria, array $orderBy = null)
 * @method InvoiceOrmEntity[]    findAll()
 * @method InvoiceOrmEntity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InvoiceOrmEntityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InvoiceOrmEntity::class);
    }

//    /**
//     * @return InvoiceOrmEntity[] Returns an array of InvoiceOrmEntity objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('i.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?InvoiceOrmEntity
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
