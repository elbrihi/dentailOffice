<?php

namespace DentalOffice\InvoiceBundle\Infrastructure\Persistence\Doctrine\Provider\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use DentalOffice\InvoiceBundle\Infrastructure\Persistence\Doctrine\Repository\InvoiceRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;


class InvoicesGetCollectionProvider implements ProviderInterface
{

    public function __construct(private InvoiceRepository $invoiceRepository)
    {
        
    }
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Paginator
    {

        $qb  = $this->invoiceRepository->createQueryBuilder('i')
                ->orderBy('i.id','DESC');


        $page = $context["filters"]["page"] ?? 1;
        $itemsPerPage = $context["filters"]["itemsPerPage"] ?? 30;


       

       
        if(isset($context["filters"]["invoiceNumber"]))
        {
            $invoiceNumber = $context["filters"]["invoiceNumber"] ;
            $qb->andWhere('i.invoiceNumber= :invoiceNumber')
            ->setParameter('invoiceNumber',$invoiceNumber);

            $firstResult = ($page - 1) * $itemsPerPage;

            $qb->setFirstResult($firstResult)
            ->setMaxResults($itemsPerPage);

            //dd($qb->getQuery()->getSQL(), $qb->getParameters());

            $paginator = new Paginator($qb->getQuery());
            
            //dd(iterator_to_array($paginator));


            return new Paginator($qb->getQuery());

          
        }
        if (isset($context["filters"]["after_invoice_date"]) && isset($context["filters"]["befor_invoice_date"])) {
            $afterDate = new \DateTimeImmutable($context['filters']['after_invoice_date']);
            $beforeDate = new \DateTimeImmutable($context['filters']['befor_invoice_date'] . ' 23:59:59'); // End of day

            $qb->andWhere('i.invoiceDate BETWEEN :afterDate AND :beforeDate')
                ->setParameter('afterDate', $afterDate)
                ->setParameter('beforeDate', $beforeDate);
        } elseif (isset($context["filters"]["befor_invoice_date"])) {
            $beforeDate = new \DateTimeImmutable($context['filters']['befor_invoice_date'] . ' 23:59:59');

            $qb->andWhere('i.invoiceDate <= :beforeDate')
                ->setParameter('beforeDate', $beforeDate);
        } elseif (isset($context["filters"]["after_invoice_date"])) {
            $afterDate = new \DateTimeImmutable($context['filters']['after_invoice_date']);

            $qb->andWhere('i.invoiceDate >= :afterDate')
                ->setParameter('afterDate', $afterDate);
        }

        $firstResult = ($page - 1) * $itemsPerPage;

        $qb->setFirstResult($firstResult)
        ->setMaxResults($itemsPerPage);

        return new Paginator($qb->getQuery());
    }
}
