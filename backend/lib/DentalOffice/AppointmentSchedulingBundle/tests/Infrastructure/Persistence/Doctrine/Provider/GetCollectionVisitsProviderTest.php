<?php

namespace DentalOffice\AppointmentSchedulingBundle\Tests\Functional\Domail\Entity;

use ApiPlatform\Metadata\GetCollection;
use DentalOffice\AppointmentSchedulingBundle\Domain\Entity\Visit;
use DentalOffice\AppointmentSchedulingBundle\Tests\VisitApiTestCase;

class GetCollectionVisitsProviderTest extends VisitApiTestCase
{
   
    
    function testGetVisitsCollection()
    {
        $this-> saveMedicalRecord();
        $this->saveVisitsForGetCollections();

        $operation = new GetCollection(); 

        $context["filters"]["page"] = 1;
        $context["filters"]["itemsPerPage"] = 8;
        $context["filters"]["befor_vist_date"] = '2024-05-15';
        $context["filters"]["after_vist_date"] = '2023-01-01';

     

        //$this->visitsGetCollectionProvider->provide($operation,[],$context);
        $paginator = $this->visitsGetCollectionProvider->provide($operation, [], $context);
        $visits = iterator_to_array($paginator);

        dd($visits);
        $this->assertCount(5, $visits); // assuming 5 were created and should match page size
        $this->assertInstanceOf(Visit::class, $visits[0]);
        // public function provide(Operation $operation, array $uriVariables = [], array $context = [])

    }
}  