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
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): iterable 
    {

    //dd($qb->getQuery()->getSQL(), $qb->getParameters());

        $qb = $this->visitRepository->createQueryBuilder('v')
            ->orderBy('v.id', 'DESC');

        $page = $context["filters"]["page"] ?? 1;
        $itemsPerPage = $context["filters"]["itemsPerPage"] ?? 30;

        if(isset($context["filters"]["befor_visit_date"]) &&
        isset($context["filters"]["after_visit_date"]))
        {
            $afterDate = new \DateTimeImmutable($context["filters"]["after_visit_date"]);
            $beforeDate = new \DateTimeImmutable($context['filters']['befor_visit_date'] . ' 23:59:59'); // End of day

            $qb->andWhere('v.visitDate BETWEEN :afterDate AND :beforeDate')
                ->setParameter('afterDate', $afterDate)
                ->setParameter('beforeDate', $beforeDate);


        }elseif(isset($context["filters"]["after_visit_date"]))
        {
            $afterDate = new \DateTimeImmutable($context["filters"]["after_visit_date"]);

            $qb->andWhere('v.visitDate >= :afterDate')
                ->setParameter('afterDate', $afterDate);
        }
        elseif(isset($context["filters"]["befor_visit_date"]))
        {
            $beforDate = new \DateTimeImmutable($context["filters"]["befor_visit_date"] . ' 23:59:59');

            $qb->andWhere('v.visitDate <= :beforDate') // ✅ Corrigé ici
            ->setParameter('beforDate', $beforDate);
        }

        
        
        $firstResult = ($page - 1) * $itemsPerPage;

        $qb->setFirstResult($firstResult)
        ->setMaxResults($itemsPerPage);

        $paginator = new Paginator($qb->getQuery());


        return $paginator ;
     //   return iterator_to_array($paginator); // <-- returns only current page items
    }
}

// iterable