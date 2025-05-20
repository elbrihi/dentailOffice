<?php
namespace DentalOffice\AppointmentSchedulingBundle\Tests\Functional\Domail\Entity;

use DentalOffice\AppointmentSchedulingBundle\Tests\VisitApiTestCase;
use DentalOffice\UserBundle\Domain\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class CreateVisitPostIntegrationTest extends VisitApiTestCase
{



    public function  testVisitProcessPersists()
    {
       $this->saveMedicalRecord();

        $client = static::createClient();

        $medicalRecordId = static::$medicalRecordId;



        $client->request(
            'POST',
            "/api/create/medicalRecord/$medicalRecordId/visit",
            [], // parameters
            [], // files
            [   // server headers
                'CONTENT_TYPE' => 'application/ld+json',
                'HTTP_AUTHORIZATION' => 'Bearer api_token_test',
            ],
            json_encode([
                "visit_date" => "2025-02-12",
                "notes" => "Consultation initiale + radio",
                "amount_paid" => 300,
                "remaining_due_after_visit" => 700
            ])
        );

    }

}