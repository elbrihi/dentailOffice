<?php

namespace DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use DentalOffice\AppointmentSchedulingBundle\Application\Dto\VisitInputDto;
use DentalOffice\AppointmentSchedulingBundle\Domain\Repository\VisitRepository;
use DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Processor\State\VisitDeleteProcessor;
use DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Processor\State\VisitPutStateProcessor;
use DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Provider\State\VisitPostProvider;
use DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Provider\State\VisitsGetCollectionProvider;
use DentalOffice\AppointmentSchedulingBundle\Presentation\Api\Processor\State\VisitPostStateProcessor;
use DentalOffice\MedicalRecordBundle\Infrastructure\Persistence\Doctrine\Entity\MedicalRecordOrmEntity;
use DentalOffice\MedicalRecordBundle\Infrastructure\Persistence\Doctrine\Entity\PrescriptionOrmEntity;
use DentalOffice\UserBundle\Domain\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

#[ORM\Table(
    name: "visit",
    indexes: [
       
    ]
)]
#[ORM\Entity(repositoryClass: VisitRepository::class)]

#[ApiResource(
    order: ['id' => 'DESC'],
    operations:[
        new Post(
            input:VisitInputDto::class,
            security: "is_granted('ROLE_ADMIN')",
            uriTemplate: "/create/medicalRecord/{medicalRecordId}/visit",
            uriVariables: [
                'medicalRecordId' => new Link( // ✅ fixed spelling
                    fromClass: MedicalRecordOrmEntity::class,
                    toProperty: 'medicalRecord'
                ),
                
            ],
            processor: VisitPostStateProcessor::class,
            provider:VisitPostProvider::class,
            normalizationContext: ['groups' => 'visit:write'],
            denormalizationContext: ['groups' => 'visit:read']
            
        ),
        new Put(
            input:VisitInputDto::class,
            security: "is_granted('ROLE_ADMIN')",
            uriTemplate: "/update/visit/{id}",
            processor: VisitPutStateProcessor::class,
            normalizationContext: ['groups' => 'visit:write'],
            denormalizationContext: ['groups' => 'visit:read']

        ),
        new Get(
            input:VisitInputDto::class,
            security: "is_granted('ROLE_ADMIN')",
            uriTemplate: "/get/visit/{id}",
            normalizationContext: ['groups'=>'visit:write',  "enable_max_depth"=>"true"],
            denormalizationContext: ['groups'=>'visit:read'],

        ),
        new GetCollection(
            security: "is_granted('ROLE_ADMIN')",
            uriTemplate: "/get/visits/by/paginations",
            provider: VisitsGetCollectionProvider::class,
            normalizationContext: ['groups'=>'visit:write'],
            denormalizationContext: ['groups'=>'visit:read'],
            paginationClientEnabled: true, // ✅ Allow clients to use `page`
            paginationClientItemsPerPage: true,
            paginationItemsPerPage: true,
        ),
        new Delete(
            security: "is_granted('ROLE_ADMIN')",
            uriTemplate: "/delete/visit/{id}",
            processor:VisitDeleteProcessor::class
        )
    ],
    paginationPartial: false,
)]
class VisitOrmEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
     #[Groups(['visit:read','visit:write','medical_record:read','medical_record:write','patient:read','patient:write'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['visit:read','visit:write','medical_record:read','medical_record:write','patient:read','patient:write'])]
    private ?string $notes = null;

    #[ORM\Column]
    #[Groups(['visit:read','visit:write','medical_record:read','medical_record:write','patient:read','patient:write'])]
    private ?float $amountPaid = null;

    #[ORM\Column(nullable:true)]
    #[Groups(['visit:read','visit:write','medical_record:read','medical_record:write','patient:read','patient:write'])]
    private ?float $remainingDueAfterVisit = null;

    #[ORM\ManyToOne(inversedBy: 'visits')]
     #[Groups(['visit:read','visit:write','medical_record:read','medical_record:write','patient:read','patient:write'])]
    #[MaxDepth(1)]
    private ?MedicalRecordOrmEntity $medicalRecord = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['visit:read','visit:write','medical_record:read','medical_record:write','patient:read','patient:write'])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column]
    #[Groups(['visit:read','visit:write','medical_record:read','medical_record:write','patient:read','patient:write'])]
    private ?\DateTimeImmutable $modifiedAt = null;

    #[ORM\ManyToOne(inversedBy: 'visits', cascade: ['persist'])]
    #[Groups(['visit:read','visit:write','medical_record:read','medical_record:write','patient:read','patient:write'])]
    private ?User $createdBy = null;

    #[ORM\ManyToOne(inversedBy: 'visits', cascade: ['persist'])]
    #[Groups(['visit:read','visit:write','medical_record:read','medical_record:write','patient:read','patient:write'])]
    private ?User $modifiedBy = null;



    #[ORM\Column]
    private ?bool $status = null;

    #[ORM\Column(length: 255)]
    #[Groups(['visit:read','visit:write','medical_record:read','medical_record:write','patient:read','patient:write'])]
    private ?string $type = null;


    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $start = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $end = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?AppointmentOrmEntity $appointment = null;

    #[ORM\OneToMany(targetEntity: PrescriptionOrmEntity::class, mappedBy: 'visitOrmEntity')]
    private Collection $prescription;

    public function __construct()
    {
        $this->prescription = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getMedicalRecord(): ?MedicalRecordOrmEntity
    {
        return $this->medicalRecord;
    }

    public function setMedicalRecord(?MedicalRecordOrmEntity $medicalRecord): static
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



    public function getStart(): ?\DateTimeInterface
    {
        return $this->start;
    }

    public function setStart(\DateTimeInterface $start): static
    {
        $this->start = $start;

        return $this;
    }

    public function getEnd(): ?\DateTimeInterface
    {
        return $this->end;
    }

    public function setEnd(\DateTimeInterface $end): static
    {
        $this->end = $end;

        return $this;
    }

    public function getAppointment(): ?AppointmentOrmEntity
    {
        return $this->appointment;
    }

    public function setAppointment(?AppointmentOrmEntity $appointment): static
    {
        $this->appointment = $appointment;

        return $this;
    }

    /**
     * @return Collection<int, PrescriptionOrmEntity>
     */
    public function getPrescription(): Collection
    {
        return $this->prescription;
    }

    public function addPrescription(PrescriptionOrmEntity $prescription): static
    {
        if (!$this->prescription->contains($prescription)) {
            $this->prescription->add($prescription);
            $prescription->setVisitOrmEntity($this);
        }

        return $this;
    }

    public function removePrescription(PrescriptionOrmEntity $prescription): static
    {
        if ($this->prescription->removeElement($prescription)) {
            // set the owning side to null (unless already changed)
            if ($prescription->getVisitOrmEntity() === $this) {
                $prescription->setVisitOrmEntity(null);
            }
        }

        return $this;
    }

  
}
