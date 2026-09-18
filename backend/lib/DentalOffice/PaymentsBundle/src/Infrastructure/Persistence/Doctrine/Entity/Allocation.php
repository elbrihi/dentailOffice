<?php

namespace DentalOffice\PaymentsBundle\Infrastructure\Persistence\Doctrine\Entity;

use DentalOffice\PaymentsBundle\Infrastructure\Persistence\Doctrine\Repository\AllocationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AllocationRepository::class)]
class Allocation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $amount = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): static
    {
        $this->amount = $amount;

        return $this;
    }
}
