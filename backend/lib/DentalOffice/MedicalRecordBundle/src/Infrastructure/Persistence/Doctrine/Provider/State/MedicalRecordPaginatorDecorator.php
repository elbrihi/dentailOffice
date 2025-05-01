<?php

namespace DentalOffice\MedicalRecordBundle\Infrastructure\Persistence\Doctrine\Provider\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;

class MedicalRecordPaginatorDecorator implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        // Retrieve the state from somewhere
    }
}
