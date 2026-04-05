<?php

namespace DentalOffice\InvoiceBundle\Infrastructure\Persistence\Doctrine\Repository;

use DentalOffice\InvoiceBundle\Infrastructure\Persistence\Doctrine\Entity\InvoiceItemOrmEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InvoiceItemOrmEntity>
 *
 * @method InvoiceItemOrmEntity|null find($id, $lockMode = null, $lockVersion = null)
 * @method InvoiceItemOrmEntity|null findOneBy(array $criteria, array $orderBy = null)
 * @method InvoiceItemOrmEntity[]    findAll()
 * @method InvoiceItemOrmEntity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InvoiceItemOrmEntityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InvoiceItemOrmEntity::class);
    }

//    /**
//     * @return InvoiceItemOrmEntity[] Returns an array of InvoiceItemOrmEntity objects
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

//    public function findOneBySomeField($value): ?InvoiceItemOrmEntity
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
