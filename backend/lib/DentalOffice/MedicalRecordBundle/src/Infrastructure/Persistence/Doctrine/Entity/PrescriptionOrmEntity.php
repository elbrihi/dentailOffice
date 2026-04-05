<?php

namespace DentalOffice\MedicalRecordBundle\Infrastructure\Persistence\Doctrine\Entity;

use DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Entity\VisitOrmEntity;
use DentalOffice\MedicalRecordBundle\Infrastructure\Persistence\Doctrine\Repository\PrescriptionOrmEntityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PrescriptionOrmEntityRepository::class)]

#[ORM\Table(
    name: "prescription",
    indexes: [
       
    ]
)]
class PrescriptionOrmEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $medication = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $dosage = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
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
