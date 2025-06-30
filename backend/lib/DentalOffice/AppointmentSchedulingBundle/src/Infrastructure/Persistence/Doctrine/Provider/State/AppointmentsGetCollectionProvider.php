<?php

namespace DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Provider\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use DentalOffice\AppointmentSchedulingBundle\Domain\Repository\AppointmentRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;


class AppointmentsGetCollectionProvider implements ProviderInterface
{

    public function __construct(
        private AppointmentRepository $repository
    )
    {
        
    }
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Paginator
    {

        $qb = $this->repository->createQueryBuilder('a')
              ->orderBy('a.id','DESC');
    
       // $sql = $qb->getQuery()->getSQL();

        // Handle pagination manually from $context
        $page = $context["filters"]["page"] ?? 1;
        $itemsPerPage = $context["filters"]["itemsPerPage"] ?? 30; // default 30 if not set
        
       
        $firstResult = ($page - 1) * $itemsPerPage;

        // Set pagination options
        $qb->setFirstResult($firstResult)
            ->setMaxResults($itemsPerPage);

        
        return new Paginator($qb);
        
       
    }
}
