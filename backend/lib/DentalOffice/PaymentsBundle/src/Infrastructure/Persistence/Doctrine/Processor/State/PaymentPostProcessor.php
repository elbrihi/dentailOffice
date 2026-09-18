<?php

namespace DentalOffice\PaymentsBundle\Infrastructure\Persistence\Doctrine\Processor\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PaymentPostProcessor implements ProcessorInterface
{
    public function __construct(
        private ProcessorInterface $persistProcessor,
        private ProcessorInterface $removeProcessor,
        private ValidatorInterface $validator
    ) {}
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
       
    }
}
