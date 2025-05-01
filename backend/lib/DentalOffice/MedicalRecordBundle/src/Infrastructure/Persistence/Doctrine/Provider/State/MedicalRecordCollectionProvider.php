<?php

namespace DentalOffice\MedicalRecordBundle\Infrastructure\Persistence\Doctrine\Provider\State;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use ApiPlatform\State\ProviderInterface;
use ApiPlatform\Metadata\Operation;
use DentalOffice\MedicalRecordBundle\Domain\Repository\MedicalRecordRepository;

class MedicalRecordCollectionProvider implements ProviderInterface
{
    private EntityManagerInterface $em;
    private MedicalRecordRepository $repository;

    public function __construct(EntityManagerInterface $em, MedicalRecordRepository $repository)
    {
        $this->em = $em;
        $this->repository = $repository;
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Paginator
    {

        //var_dump($qb->getQuery()->getDQL());die;

        // Create the query builder
        $qb = $this->repository->createQueryBuilder('m')
            ->orderBy('m.id', 'DESC'); // You can add filters here

        // Handle pagination manually from $context
        $page = $context["filters"]["page"] ?? 1;
        $itemsPerPage = $context["filters"]["itemsPerPage"] ?? 30; // default 30 if not set

        
        // Chief Complaint partial search
        if (isset($context['filters']['chief_complaint'])) {
            $chiefComplaint = $context['filters']['chief_complaint'];

            $qb->andWhere('m.chief_complaint LIKE :chief_complaint')
            ->setParameter('chief_complaint', '%' . $chiefComplaint . '%');
        }

        // Filter by start_date
        if (!empty($context['filters']['visit_date_start_date'])) {
            $startDate = new \DateTime($context['filters']['visit_date_start_date']);
            $qb->andWhere('m.visit_date >= :start_date')
                ->setParameter('start_date', $startDate->format('Y-m-d') . ' 00:00:00');
        }

        // Filter by end_date
        if (!empty($context['filters']['visit_date_end_date'])) {
            $endDate = new \DateTime($context['filters']['visit_date_end_date']);
            $qb->andWhere('m.visit_date <= :end_date')
                ->setParameter('end_date', $endDate->format('Y-m-d') . ' 23:59:59');
        }

        $firstResult = ($page - 1) * $itemsPerPage;

        // Set pagination options
        $qb->setFirstResult($firstResult)
            ->setMaxResults($itemsPerPage);

        // Return the paginator instance with the query builder
        return new Paginator($qb);
    }

    private function applyFilters($qb, array $filters): void
    {
        foreach ($filters as $field => $value) {
            if (empty($value)) {
                continue;
            }

            switch ($field) {
                case 'chief_complaint':
                    $qb->andWhere('m.chief_complaint LIKE :chief_complaint')
                       ->setParameter('chief_complaint', '%' . $value . '%');
                    break;

                case 'start_date':
                    $startDate = new \DateTime($value);
                    $qb->andWhere('m.createdAt >= :start_date')
                       ->setParameter('start_date', $startDate->format('Y-m-d') . ' 00:00:00');
                    break;

                case 'end_date':
                    $endDate = new \DateTime($value);
                    $qb->andWhere('m.createdAt <= :end_date')
                       ->setParameter('end_date', $endDate->format('Y-m-d') . ' 23:59:59');
                    break;

                // 👉 Add more filters here easily later
                default:
                    // Ignore unknown filters
                    break;
            }
        }
    }
}

