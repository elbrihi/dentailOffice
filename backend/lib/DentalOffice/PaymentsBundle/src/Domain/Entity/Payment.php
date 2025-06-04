<?php

namespace DentalOffice\PaymentsBundle\Domain\Entity;

use DentalOffice\AppointmentSchedulingBundle\Domain\Entity\Visit;
use DentalOffice\InvoiceBundle\Domain\Entity\Invoice;
use DentalOffice\PaymentsBundle\Domain\Repository\PaymentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PaymentRepository::class)]
class Payment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $amount = null;

    #[ORM\Column(length: 255)]
    private ?string $method = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $paymentDate = null;

    #[ORM\ManyToOne(inversedBy: 'payments')]
    private ?Visit $visit = null;

    #[ORM\ManyToOne(targetEntity: Invoice::class, inversedBy: 'payments')]
    private ?Invoice $invoice = null;

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

    public function getMethod(): ?string
    {
        return $this->method;
    }

    public function setMethod(string $method): static
    {
        $this->method = $method;

        return $this;
    }

    public function getPaymentDate(): ?\DateTimeInterface
    {
        return $this->paymentDate;
    }

    public function setPaymentDate(\DateTimeInterface $paymentDate): static
    {
        $this->paymentDate = $paymentDate;

        return $this;
    }

    public function getVisit(): ?Visit
    {
        return $this->visit;
    }

    public function setVisit(?Visit $visit): static
    {
        $this->visit = $visit;

        return $this;
    }

    public function setInvoice(?Invoice $invoice): static
    {
        $this->invoice = $invoice;

        return $this;
    }

    public function getInvoice(): ?Invoice
    {
        return $this->invoice;
    }
}
