<?php

namespace DentalOffice\MedicalRecordBundle\Infrastructure\Persistence\Doctrine\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Entity\VisitOrmEntity;
use DentalOffice\MedicalRecordBundle\Infrastructure\Persistence\Doctrine\Repository\PrescriptionOrmEntityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PrescriptionOrmEntityRepository::class)]

#[ORM\Table(
    name: "prescription",
    indexes: [
       
    ]
)]
#[ApiResource(

    order: ['id'=>'DESC'],
    operations: [
            new Get(
                security: "is_granted('ROLE_OWNER')",
                normalizationContext:['groups' => 'prescription:write'],
                denormalizationContext: ['groups' => 'prescription:read'],

            )
    ]

)
]
class PrescriptionOrmEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['medical_record:read','medical_record:write','patient:read','patient:write'])]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['medical_record:read','medical_record:write','patient:read','patient:write'])]
    private ?string $medication = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['medical_record:read','medical_record:write','patient:read','patient:write'])]
    private ?string $dosage = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['medical_record:read','medical_record:write','patient:read','patient:write'])]
    private ?string $notes = null;

    #[ORM\ManyToOne(inversedBy: 'prescription')]
    private ?VisitOrmEntity $visitOrmEntity = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMedication(): ?string
    {
        return $this->medication;
    }

    public function setMedication(?string $medication): static
    {
        $this->medication = $medication;

        return $this;
    }

    public function getDosage(): ?string
    {
        return $this->dosage;
    }

    public function setDosage(?string $dosage): static
    {
        $this->dosage = $dosage;

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

    public function getVisitOrmEntity(): ?VisitOrmEntity
    {
        return $this->visitOrmEntity;
    }

    public function setVisitOrmEntity(?VisitOrmEntity $visitOrmEntity): static
    {
        $this->visitOrmEntity = $visitOrmEntity;

        return $this;
    }
}
