<?php

namespace DentalOffice\AppointmentSchedulingBundle\Application\Dto;

use DateTimeInterface;
use DentalOffice\MedicalRecordBundle\Infrastructure\Persistence\Doctrine\Entity\MedicalRecordOrmEntity;
use DentalOffice\PaymentsBundle\Domain\Entity\Payment;
use DentalOffice\UserBundle\Domain\Entity\User;
use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

class VisitInputDto
{
    private ?int $id = null;

    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'])]
    private ?DateTimeInterface $visitDate = null;
    private ?string $notes = null;
    private ?float $amountPaid = null;
    private ?float $remainingDueAfterVisit = null;
    private ?MedicalRecordOrmEntity $medicalRecord = null;
    private ?DateTimeInterface $createdAt = null;
    private ?\DateTimeImmutable $modifiedAt = null;
    private ?User $createdBy = null;
    private ?User $modifiedBy = null;
    private ?int $durationMinutes = null;
    private ?bool $status = null;
    private ?string $type = null;

    /**
     * @var array<int, array> Each payment is an associative array (or you can use a PaymentDto)
     */
    private array $payments = [];

    // === Getters and Setters ===

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): static
    {
        $this->id = $id;
        return $this;
    }

    public function getVisitDate(): ?DateTimeInterface
    {
        return $this->visitDate;
    }

    public function setVisitDate(?DateTimeInterface $visitDate): static
    {
        $this->visitDate = $visitDate;
        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;
        return $this;
    }

    public function getAmountPaid(): ?float
    {
        return $this->amountPaid;
    }

    public function setAmountPaid(?float $amountPaid): static
    {
        $this->amountPaid = $amountPaid;
        return $this;
    }

    public function getRemainingDueAfterVisit(): ?float
    {
        return $this->remainingDueAfterVisit;
    }

    public function setRemainingDueAfterVisit(?float $remainingDueAfterVisit): static
    {
        $this->remainingDueAfterVisit = $remainingDueAfterVisit;
        return $this;
    }

    public function getMedicalRecord(): ?MedicalRecordOrmEntity
    {
        return $this->medicalRecord;
    }

    public function setMedicalRecord(?MedicalRecordOrmEntity $medicalRecord): static
    {
        $this->medicalRecord = $medicalRecord;
        return $this;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getModifiedAt(): ?\DateTimeImmutable
    {
        return $this->modifiedAt;
    }

    public function setModifiedAt(?\DateTimeImmutable $modifiedAt): static
    {
        $this->modifiedAt = $modifiedAt;
        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): static
    {
        $this->createdBy= $createdBy;
        return $this;
    }

    public function getModifiedBy(): ?User
    {
        return $this->modifiedBy;
    }

    public function setModifiedBy(?User $modifiedBy): static
    {
        $this->modifiedBy = $modifiedBy;
        return $this;
    }

    public function getDurationMinutes(): ?int
    {
        return $this->durationMinutes;
    }

    public function setDurationMinutes(?int $durationMinutes): static
    {
        $this->durationMinutes = $durationMinutes;
        return $this;
    }

    public function getStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(?bool $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): static
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return array<int, array>
     */
    public function getPayments(): array
    {
        return $this->payments;
    }

    /**
     * @param array<int, array> $payments
     */
    public function setPayments(array $payments): static
    {
        $this->payments = $payments;
        return $this;
    }

    public function addPayment(Payment $payment): static
    {
        $this->payments[] = $payment;
        return $this;
    }

    
}