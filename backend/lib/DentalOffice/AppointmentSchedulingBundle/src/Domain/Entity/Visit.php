<?php

namespace DentalOffice\AppointmentSchedulingBundle\Domain\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Get;
use DentalOffice\AppointmentSchedulingBundle\Domain\Repository\VisitRepository;
use DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Processor\State\VisitPostStateProcessor;
use DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Processor\State\VisitPutStateProcessor;
use DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Provider\State\VisitPostStateProvider;
use DentalOffice\MedicalRecordBundle\Domain\Entity\MedicalRecord;
use DentalOffice\UserBundle\Domain\Entity\User;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#
#[ORM\Entity(repositoryClass: VisitRepository::class)]

#[ApiResource(
    order: ['id' => 'DESC'],
    operations:[
        new Post(
            security: "is_granted('ROLE_ADMIN')",
            uriTemplate: "/create/medicalRecord/{medicalRecordId}/visit",
            uriVariables: [
                'medicalRecordId' => new Link( // ✅ fixed spelling
                    fromClass: MedicalRecord::class,
                    toProperty: 'medicalRecord'
                ),
                
            ],
            provider: VisitPostStateProvider::class,
            processor: VisitPostStateProcessor::class,
            normalizationContext: ['groups' => 'visit:write'],
            denormalizationContext: ['groups' => 'visit:read']
            
        ),
        new Put(
            security: "is_granted('ROLE_ADMIN')",
            uriTemplate: "/update/visit/{visitId}",
            provider: VisitPostStateProvider::class,
            processor: VisitPutStateProcessor::class
        ),
        new Get(
            security: "is_granted('ROLE_ADMIN')",
            uriTemplate: "/get/visit/{id}",
            provider: VisitPostStateProvider::class,
            processor: VisitPostStateProcessor::class

        ),
        
    ],
    paginationPartial: false,
)]
class Visit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $visitDate = null;

    #[ORM\Column(length: 255)]
    private ?string $notes = null;

    #[ORM\Column]
    private ?float $amountPaid = null;

    #[ORM\Column]
    private ?float $remainingDueAfterVisit = null;

    #[ORM\ManyToOne(inversedBy: 'visits')]
    private ?MedicalRecord $medicalRecord = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $modifiedAt = null;

    #[ORM\ManyToOne(inversedBy: 'visits')]
    private ?User $createdBy = null;

    #[ORM\ManyToOne(inversedBy: 'visits')]
    private ?User $modifiedBy = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVisitDate(): ?\DateTimeInterface
    {
        return $this->visitDate;
    }

    public function setVisitDate(\DateTimeInterface $visitDate): static
    {
        $this->visitDate = $visitDate;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(string $notes): static
    {
        $this->notes = $notes;

        return $this;
    }

    public function getAmountPaid(): ?float
    {
        return $this->amountPaid;
    }

    public function setAmountPaid(float $amountPaid): static
    {
        $this->amountPaid = $amountPaid;

        return $this;
    }

    public function getRemainingDueAfterVisit(): ?float
    {
        return $this->remainingDueAfterVisit;
    }

    public function setRemainingDueAfterVisit(float $remainingDueAfterVisit): static
    {
        $this->remainingDueAfterVisit = $remainingDueAfterVisit;

        return $this;
    }

    public function getMedicalRecord(): ?MedicalRecord
    {
        return $this->medicalRecord;
    }

    public function setMedicalRecord(?MedicalRecord $medicalRecord): static
    {
        $this->medicalRecord = $medicalRecord;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getModifiedAt(): ?\DateTimeImmutable
    {
        return $this->modifiedAt;
    }

    public function setModifiedAt(\DateTimeImmutable $modifiedAt): static
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
        $this->createdBy = $createdBy;

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
}
