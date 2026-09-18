<?php

namespace DentalOffice\MedicalRecordBundle\Infrastructure\Persistence\Doctrine\Entity;

use DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Entity\VisitOrmEntity;
use DentalOffice\MedicalRecordBundle\Infrastructure\Persistence\Doctrine\Repository\StepOrmEntityRepository;
use DentalOffice\UserBundle\Domain\Entity\User;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StepOrmEntityRepository::class)]
#[ORM\Table(
    name: "step",
)]
class StepOrmEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;


    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $performed_at = null;

    #[ORM\OneToOne(cascade: ['persist'])]
    private ?VisitOrmEntity $visit = null;

    #[ORM\ManyToOne(inversedBy: 'step')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'steps')]
    #[ORM\JoinColumn(name: "treatment_id", referencedColumnName: "id")]
    private ?TreatmentOrmEntity $treatmentOrmEntity = null;

    #[ORM\Column]
    private ?float $amount = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

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

    public function getPerformedAt(): ?\DateTimeInterface
    {
        return $this->performed_at;
    }

    public function setPerformedAt(\DateTimeInterface $performed_at): static
    {
        $this->performed_at = $performed_at;

        return $this;
    }

    public function getVisit(): ?VisitOrmEntity
    {
        return $this->visit;
    }

    public function setVisit(?VisitOrmEntity $visit): static
    {
        $this->visit = $visit;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getTreatmentOrmEntity(): ?TreatmentOrmEntity
    {
        return $this->treatmentOrmEntity;
    }

    public function setTreatmentOrmEntity(?TreatmentOrmEntity $treatmentOrmEntity): static
    {
        $this->treatmentOrmEntity = $treatmentOrmEntity;

        return $this;
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
