<?php

namespace DentalOffice\InvoiceBundle\Infrastructure\Persistence\Doctrine\Entity;

use DentalOffice\InvoiceBundle\Infrastructure\Persistence\Doctrine\Repository\InvoiceItemOrmEntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

#[ORM\Entity(repositoryClass: InvoiceItemOrmEntityRepository::class)]
#[ORM\Table(name: "invoice_item")]
class InvoiceItemOrmEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['invoice:read','medical_record:read','patient:read','patient:write'])]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['invoice:read','medical_record:read','patient:read','patient:write'])]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['invoice:read','medical_record:read','patient:read','patient:write'])]
    private ?float $amount = null;

    #[ORM\ManyToOne(inversedBy: 'invoiceItem')]
    #[Groups(['invoice:read','medical_record:read','patient:read','patient:write'])]
    private ?InvoiceOrmEntity $invoiceOrmEntity = null;

    #[ORM\Column(length: 20)]
    #[Groups(['invoice:read','medical_record:read','patient:read','patient:write'])]
    private ?string $status = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(?float $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getInvoiceOrmEntity(): ?InvoiceOrmEntity
    {
        return $this->invoiceOrmEntity;
    }

    public function setInvoiceOrmEntity(?InvoiceOrmEntity $invoiceOrmEntity): static
    {
        $this->invoiceOrmEntity = $invoiceOrmEntity;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }
}
