<?php

namespace DentalOffice\MedicalRecordBundle\Infrastructure\Persistence\Doctrine\Entity;

use DentalOffice\MedicalRecordBundle\Infrastructure\Persistence\Doctrine\Repository\InvoiceOrmEntityRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InvoiceOrmEntityRepository::class)]
class InvoiceOrmEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $executionStatus = null;

    #[ORM\Column(length: 255)]
    private ?string $paymentStatus = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getExecutionStatus(): ?string
    {
        return $this->executionStatus;
    }

    public function setExecutionStatus(string $executionStatus): static
    {
        $this->executionStatus = $executionStatus;

        return $this;
    }

    public function getPaymentStatus(): ?string
    {
        return $this->paymentStatus;
    }

    public function setPaymentStatus(string $paymentStatus): static
    {
        $this->paymentStatus = $paymentStatus;

        return $this;
    }
}
