<?php

namespace DentalOffice\MedicalRecordBundle\Infrastructure\Persistence\Doctrine\Provider\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class PostMedicalRecordProvider implements ProviderInterface
{
    
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.item_provider')]
        private ProviderInterface $itemProvider,
    )
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        // Retrieve the state from somewhere
        $request = $context["request"];

        return null;
    }
}
