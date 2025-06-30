<?php
namespace DentalOffice\AppointmentSchedulingBundle\Tests\Infrastructure\Persistence\Doctrine\Processor\State;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use DentalOffice\AppointmentSchedulingBundle\Domain\Entity\Visit;
use DentalOffice\AppointmentSchedulingBundle\Tests\VisitApiTestCase;
use Symfony\Component\HttpFoundation\Request;

class VisitDeleteProcessorTest extends VisitApiTestCase
{

    public function testDeletingVisit()
    {
        $this->saveMedicalRecord();
        $this->saveVisits();
        $this->entityManager->clear();
        $request = new Request([], [], [], [], [], [], json_encode([]));

        $visit = new Visit();
        $operation = new Delete();

        $context['request'] = $request;
     
        $uriVariables['visitId'] =static::$visitId;


        $this->visitDeleteStateProcessor->process($visit, $operation, $uriVariables, $context);

        $deletedVisit = $this->entityManager->getRepository(Visit::class)->find(static::$visitId);

        // Assert the visit no longer exists
        $this->assertNull($deletedVisit, 'Visit should be deleted and no longer exist in the database');

        
    }
}