<?php

namespace DentalOffice\PatientBundle\Infrastucture\Persistence\Doctrine\Provider\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\PaginatorInterface;
use ApiPlatform\State\ProviderInterface;
use DentalOffice\PatientBundle\Domain\Repository\PatientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;

class PatientGetCollectionProvider implements ProviderInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private PatientRepository $repository
        )
    {
        
    }
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Paginator
    {

        $qb = $this->repository->createQueryBuilder('p')
        ->orderBy('p.id', 'DESC'); // You can add filters here

        
        // Handle pagination manually from $context
        $page = $context["filters"]["page"] ?? 1;
        $itemsPerPage = $context["filters"]["itemsPerPage"] ?? 30; // default 30 if not set
        
       
        // Chief Complaint partial search
        if (isset($context['filters']['cni'])) {
            $cni = $context['filters']['cni'];

            $qb->andWhere('p.cni LIKE :cni')
            ->setParameter('cni', '%' . $cni . '%');
        }
        // Filter by start_date
        if (!empty($context['filters']['createdAt_start_date'])) {
            $startDate = new \DateTime($context['filters']['createdAt_start_date']);
            $qb->andWhere('p.createdAt >= :start_date')
                ->setParameter('start_date', $startDate->format('Y-m-d') . ' 00:00:00');
        }

        // Filter by end_date
        if (!empty($context['filters']['createdAt_end_date'])) {
            $endDate = new \DateTime($context['filters']['createdAt_end_date']);
            $qb->andWhere('p.createdAt <= :end_date')
                ->setParameter('end_date', $endDate->format('Y-m-d') . ' 23:59:59');
        }

        $firstResult = ($page - 1) * $itemsPerPage;

        // Set pagination options
        $qb->setFirstResult($firstResult)
            ->setMaxResults($itemsPerPage);
        
        //dd($qb->getQuery()->getSQL());

        return new Paginator($qb);
        // Retrieve the state from somewhere
    }
}
