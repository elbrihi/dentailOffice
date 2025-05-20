<?php

namespace DentalOffice\AppointmentSchedulingBundle\Domain\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Put;
use DentalOffice\AppointmentSchedulingBundle\Domain\Repository\AppointmentRepository;
use DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Processor\State\AppointmentPutProcessor;
use DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Processor\State\AppointmentStateProcessor;
use DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Provider\State\AppointmentsGetCollectionProvider;
use DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Provider\State\AppointmentStateProvider;
use DentalOffice\MedicalRecordBundle\Domain\Entity\MedicalRecord;
use DentalOffice\PatientBundle\Domain\Entity\Patient;
use DentalOffice\UserBundle\Domain\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: AppointmentRepository::class)]
#[ApiResource(
    order: ['id' => 'DESC'],
    operations:[
        new Post(
            security: "is_granted('ROLE_ADMIN')",
            uriTemplate: "/create/patient/{patientId}/appointment",
            uriVariables: [
                'patientId' => new Link(
                    fromClass: Patient::class,
                    toProperty: 'patient'
                ),
            ],
            provider: AppointmentStateProvider::class,
            processor: AppointmentStateProcessor::class,
            normalizationContext: ['groups'=>'appointment:write'],
            denormalizationContext: ['groups'=>'appointment:read'],
        ),
        new Get(
            security: "is_granted('ROLE_ADMIN')",
            uriTemplate: "/get/appointment/by/{id}",
            normalizationContext: ['groups'=>'appointment:write'],
            denormalizationContext: ['groups'=>'appointment:read']
        ),
        new Put(
            security: "is_granted('ROLE_ADMIN')",
            uriTemplate: "/update/appointment/{id}",
            processor: AppointmentPutProcessor::class,
            normalizationContext: ['groups'=>'appointment:write'],
            denormalizationContext: ['groups'=>'appointment:read'],
        ),
        new GetCollection(
            security: "is_granted('ROLE_ADMIN')",
            uriTemplate: "/get/appointments/by/paginations",
            processor: AppointmentsGetCollectionProvider::class,
            normalizationContext: ['groups'=>'appointment:write'],
            denormalizationContext: ['groups'=>'appointment:read'],
            paginationClientItemsPerPage: true,
            paginationItemsPerPage: true,
        ),
        
    ],
    paginationPartial: false,
)]
class Appointment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups('appointment:write','appointment:read')]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups('appointment:write','appointment:read')]
    private ?\DateTimeInterface $appointmentDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups('appointment:write','appointment:read')]
    private ?\DateTimeInterface $modifiedAt = null;

    #[ORM\Column(length: 255)]
    #[Groups('appointment:write','appointment:read')]
    private ?string $reason = null;

    #[ORM\ManyToOne(inversedBy: 'appointment', cascade: ['persist'])]
    #[Groups('appointment:write','appointment:read')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'appointments', cascade: ['persist'])]
    #[Groups('appointment:write','appointment:read')]
    private ?User $createdBy = null;

    #[ORM\ManyToOne(inversedBy: 'appointments', cascade: ['persist'])]
    #[Groups('appointment:write','appointment:read')]
    private ?User $modifiedBy = null;

    #[ORM\Column]
    #[Groups('appointment:write','appointment:read')]
    private ?bool $status = null;

    #[ORM\Column]
    #[Groups('appointment:write','appointment:read')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'appointments', cascade: ['persist', 'remove'])]
    #[Groups('appointment:write','appointment:read')]
    private ?Patient $patient = null;

    #[ORM\OneToMany(targetEntity: MedicalRecord::class, mappedBy: 'appointment')]
    private Collection $medicalRecord;





    public function __construct()
    {
        $this->medicalRecord = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAppointmentDate(): ?\DateTimeInterface
    {
        return $this->appointmentDate;
    }

    public function setAppointmentDate(\DateTimeInterface $appointmentDate): static
    {
        $this->appointmentDate = $appointmentDate;

        return $this;
    }

    
    public function getModifiedAt(): ?\DateTimeInterface
    {
        return $this->modifiedAt;
    }

    public function setModifiedAt(\DateTimeInterface $modifiedAt): static
    {
        $this->modifiedAt = $modifiedAt;

        return $this;
    }



    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(string $reason): static
    {
        $this->reason = $reason;

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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getPatient(): ?Patient
    {
        return $this->patient;
    }

    public function setPatient(?Patient $patient): static
    {
        $this->patient = $patient;

        return $this;
    }

    /**
     * @return Collection<int, MedicalRecord>
     */
    public function getMedicalRecord(): Collection
    {
        return $this->medicalRecord;
    }

    public function addMedicalRecord(MedicalRecord $medicalRecord): static
    {
        if (!$this->medicalRecord->contains($medicalRecord)) {
            $this->medicalRecord->add($medicalRecord);
            $medicalRecord->setAppointment($this);
        }

        return $this;
    }

    public function removeMedicalRecord(MedicalRecord $medicalRecord): static
    {
        if ($this->medicalRecord->removeElement($medicalRecord)) {
            // set the owning side to null (unless already changed)
            if ($medicalRecord->getAppointment() === $this) {
                $medicalRecord->setAppointment(null);
            }
        }

        return $this;
    }


}
