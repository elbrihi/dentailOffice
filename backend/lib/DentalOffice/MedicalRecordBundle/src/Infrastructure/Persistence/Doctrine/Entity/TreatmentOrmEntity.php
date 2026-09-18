<?php

namespace DentalOffice\MedicalRecordBundle\Infrastructure\Persistence\Doctrine\Entity;

use DentalOffice\MedicalRecordBundle\Infrastructure\Persistence\Doctrine\Repository\TreatmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(
    name: "treatment",
   
)]
#[ORM\Entity(repositoryClass: TreatmentRepository::class)]
class TreatmentOrmEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[ORM\ManyToOne(inversedBy: 'treatments')]
    #[ORM\JoinColumn(name: "medical_record_id", referencedColumnName: "id")]
    private ?MedicalRecordOrmEntity $medicalRecordOrmEntity = null;

    #[ORM\OneToMany(targetEntity: StepOrmEntity::class, mappedBy: 'treatmentOrmEntity', cascade: ['persist'],orphanRemoval: true)]
    #[ORM\JoinColumn(name: "treatment_id", referencedColumnName: "id")]
    private Collection $steps;

    public function __construct()
    {
        $this->steps = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

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

    public function getMedicalRecordOrmEntity(): ?MedicalRecordOrmEntity
    {
        return $this->medicalRecordOrmEntity;
    }

    public function setMedicalRecordOrmEntity(?MedicalRecordOrmEntity $medicalRecordOrmEntity): static
    {
        $this->medicalRecordOrmEntity = $medicalRecordOrmEntity;

        return $this;
    }

    /**
     * @return Collection<int, StepOrmEntity>
     */
    public function getSteps(): Collection
    {
        return $this->steps;
    }

    public function addStep(StepOrmEntity $step): static
    {
        if (!$this->steps->contains($step)) {
            $this->steps->add($step);
            $step->setTreatmentOrmEntity($this);
        }

        return $this;
    }

    public function removeStep(StepOrmEntity $step): static
    {
        if ($this->steps->removeElement($step)) {
            // set the owning side to null (unless already changed)
            if ($step->getTreatmentOrmEntity() === $this) {
                $step->setTreatmentOrmEntity(null);
            }
        }

        return $this;
    }
}
