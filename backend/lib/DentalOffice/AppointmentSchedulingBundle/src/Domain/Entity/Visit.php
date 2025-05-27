<?php

namespace DentalOffice\AppointmentSchedulingBundle\Domain\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use DentalOffice\AppointmentSchedulingBundle\Domain\Repository\VisitRepository;
use DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Processor\State\VisitPostStateProcessor;
use DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Processor\State\VisitPutStateProcessor;
use DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Provider\State\VisitsGetCollectionProvider;
use DentalOffice\MedicalRecordBundle\Domain\Entity\MedicalRecord;
use DentalOffice\PaymentsBundle\Domain\Entity\Payment;
use DentalOffice\UserBundle\Domain\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

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
            processor: VisitPostStateProcessor::class,
            normalizationContext: ['groups' => 'visit:write'],
            denormalizationContext: ['groups' => 'visit:read']
            
        ),
        new Put(
            security: "is_granted('ROLE_ADMIN')",
            uriTemplate: "/update/visit/{id}",
            processor: VisitPutStateProcessor::class,

        ),
        new Get(
            security: "is_granted('ROLE_ADMIN')",
            uriTemplate: "/get/visit/{id}",
            normalizationContext: ['groups'=>'visit:write'],
            denormalizationContext: ['groups'=>'visit:read'],

        ),
        new GetCollection(
            security: "is_granted('ROLE_ADMIN')",
            uriTemplate: "/get/visits/by/paginations",
            provider: VisitsGetCollectionProvider::class,
            normalizationContext: ['groups'=>'visit:write'],
            denormalizationContext: ['groups'=>'visit:read'],
            paginationClientItemsPerPage: true,
            paginationItemsPerPage: true,
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
    #[Groups(['visit:read','visit:write'])]
    private ?\DateTimeInterface $visitDate = null;

    #[ORM\Column(length: 255)]
    #[Groups(['visit:read','visit:write'])]
    private ?string $notes = null;

    #[ORM\Column]
    #[Groups(['visit:read','visit:write'])]
    private ?float $amountPaid = null;

    #[ORM\Column]
    #[Groups(['visit:read','visit:write'])]
    private ?float $remainingDueAfterVisit = null;

    #[ORM\ManyToOne(inversedBy: 'visits')]
    private ?MedicalRecord $medicalRecord = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['visit:read','visit:write'])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column]
    #[Groups(['visit:read','visit:write'])]
    private ?\DateTimeImmutable $modifiedAt = null;

    #[ORM\ManyToOne(inversedBy: 'visits')]
    #[Groups(['visit:read','visit:write'])]
    private ?User $createdBy = null;

    #[ORM\ManyToOne(inversedBy: 'visits')]
    #[Groups(['visit:read','visit:write'])]
    private ?User $modifiedBy = null;

    #[ORM\Column]
    private ?int $durationMinutes = null;

    #[ORM\Column]
    private ?bool $status = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\OneToMany(targetEntity: Payment::class, mappedBy: 'visit', cascade: ['remove'],)]
    private Collection $payments;

    public function __construct()
    {
        $this->payments = new ArrayCollection();
    }

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

    public function getDurationMinutes(): ?int
    {
        return $this->durationMinutes;
    }

    public function setDurationMinutes(int $durationMinutes): static
    {
        $this->durationMinutes = $durationMinutes;

        return $this;
    }

    public function isStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return Collection<int, Payment>
     */
    public function getPayments(): Collection
    {
        return $this->payments;
    }

    public function addPayment(Payment $payment): static
    {
        if (!$this->payments->contains($payment)) {
            $this->payments->add($payment);
            $payment->setVisit($this);
        }

        return $this;
    }

    public function removePayment(Payment $payment): static
    {
        if ($this->payments->removeElement($payment)) {
            // set the owning side to null (unless already changed)
            if ($payment->getVisit() === $this) {
                $payment->setVisit(null);
            }
        }

        return $this;
    }

  
}
