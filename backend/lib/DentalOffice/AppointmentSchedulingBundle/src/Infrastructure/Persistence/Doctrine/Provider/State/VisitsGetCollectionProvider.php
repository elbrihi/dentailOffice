<?php

namespace DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Provider\State;

use Doctrine\ORM\Tools\Pagination\Paginator;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use DentalOffice\AppointmentSchedulingBundle\Domain\Repository\VisitRepository;

class VisitsGetCollectionProvider implements ProviderInterface
{
    public function __construct
    (
        private VisitRepository $visitRepository
    )
    {

    }
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Paginator
    {

//dd($qb->getQuery()->getSQL(), $qb->getParameters());

    $qb = $this->visitRepository->createQueryBuilder('v')
        ->orderBy('v.id', 'DESC');

    $page = $context["filters"]["page"] ?? 1;
    $itemsPerPage = $context["filters"]["itemsPerPage"] ?? 30;

    if (isset($context["filters"]["befor_vist_date"])) {

        
        $beforeDate = new \DateTime($context['filters']['befor_vist_date']);
        $qb->andWhere('v.visitDate <= :beforeDate')
           ->setParameter('beforeDate', $beforeDate->format('Y-m-d') . ' 23:59:59');
    }

    if (isset($context["filters"]["after_vist_date"])) {
        $afterDate = new \DateTime($context['filters']['after_vist_date']);
        $qb->andWhere('v.visitDate >= :afterDate')
           ->setParameter('afterDate', $afterDate->format('Y-m-d') . ' 00:00:00');
    }

    $firstResult = ($page - 1) * $itemsPerPage;

    $qb->setFirstResult($firstResult)
       ->setMaxResults($itemsPerPage);

    return new Paginator($qb->getQuery());
    }
}
