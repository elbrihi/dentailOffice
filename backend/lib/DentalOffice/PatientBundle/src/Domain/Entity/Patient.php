<?php

namespace DentalOffice\PatientBundle\Domain\Entity;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Get;
use DentalOffice\AppointmentSchedulingBundle\Domain\Entity\Appointment;
use DentalOffice\MedicalRecordBundle\Domain\Entity\MedicalRecord;
use DentalOffice\PatientBundle\Domain\Repository\PatientRepository;
use DentalOffice\PatientBundle\Infrastructure\Persistence\Doctrine\Processor\State\PatientPostProcessor;
use DentalOffice\PatientBundle\Infrastructure\Persistence\Doctrine\Processor\State\PatientPutProcessor;
use DentalOffice\PatientBundle\Infrastucture\Persistence\Doctrine\Provider\State\PatientGetCollectionProvider;
use DentalOffice\UserBundle\Domain\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PatientRepository::class)]

#[ApiResource(
    order: ['id' => 'DESC'],
    operations:[
            new Post(
                security: "is_granted('ROLE_ADMIN')",
                uriTemplate: "/create/new/patient",
                processor: PatientPostProcessor::class,
                normalizationContext: ['groups' => ['patient:read']],
                denormalizationContext: ['groups' => ['patient:write']],
            ),
            new Put(
                security: "is_granted('ROLE_ADMIN')",
                uriTemplate: "/update/patient/{id}",
                processor: PatientPutProcessor::class,
                normalizationContext: ['groups' => ['patient:read']],
                denormalizationContext: ['groups' => ['patient:write']],
            ),
            new Get(
                uriTemplate: "/get/patient/{id}",
                normalizationContext: ['groups' => ['patient:read']],
                denormalizationContext: ['groups' => ['patient:write']],
            ),
            new GetCollection(
                uriTemplate: '/get/patients/by/paginations',
                normalizationContext: ['groups' => ['patient:read']],
                denormalizationContext: ['groups' => ['patient:write']],
                paginationClientItemsPerPage: true,
                paginationItemsPerPage: true,
                provider: PatientGetCollectionProvider::class

            ),
            new GetCollection(
                uriTemplate: '/get/patients/',
                normalizationContext: ['groups' => ['patient:read']],
                denormalizationContext: ['groups' => ['patient:write']],
            ),
        ],
        paginationPartial: false,
)]

class Patient
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['patient:read','patient:write','appointment:write','appointment:read'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $birthDate = null;

    #[ORM\Column(length: 255)]
    #[Groups(['patient:read','patient:write','medical_record:read','medical_record:write','appointment:write','appointment:read'])]
    private ?string $lastName = null;

    #[ORM\Column(length: 255)]
    #[Groups(['patient:read','patient:write','appointment:write','appointment:read'])]
    private ?string $firstName = null;


    #[ORM\Column(length: 255)]
    #[Groups(['patient:read','patient:write'])]
    private ?string $gender = null;

    #[ORM\Column(length: 255, nullable:true)]
    #[Groups(['patient:read','patient:write'])]
    private ?string $phone = null;

    #[ORM\Column(length: 255)]
    #[Groups(['patient:read','patient:write'])]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    #[Groups(['patient:read','patient:write'])]
    private ?string $address = null;

    #[ORM\Column(length: 255)]
    #[Groups(['patient:read','patient:write'])]
    private ?string $bloodType = null;

    #[ORM\Column(length: 255)]
    #[Groups(['patient:read','patient:write'])]
    private ?string $medicalHistory = null;

    #[ORM\Column(length: 255)]
    #[Groups(['patient:read','patient:write'])]
    private ?string $notes = null;

    #[ORM\Column]
    #[Groups(['patient:read','patient:write'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'patients')]
    #[Groups(['patient:read','patient:write'])]
    private ?User $createdBy = null;


    #[ORM\ManyToOne(inversedBy: 'patientdModifieds', cascade: ['persist'])]
    #[Groups(['patient:read','patient:write'])]
    private ?User $modifiedBy = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['patient:read','patient:write'])]
    private ?\DateTimeInterface $modified_at = null;

    #[ORM\Column]
    #[Groups(['patient:read','patient:write'])]
    private ?bool $status = null;

    #[ORM\Column(length: 255)]
    #[Groups(['patient:read','patient:write'])]
    private ?string $cni = null;

    #[ORM\OneToMany(targetEntity: MedicalRecord::class, mappedBy: 'patient', cascade: ['persist', 'remove'])]
    #[Groups(['patient:read','patient:write'])]
    private Collection $medicalRecord;

    #[ORM\OneToMany(targetEntity: Appointment::class, mappedBy: 'patient' , cascade: ['persist', 'remove'])]
    #[Groups(['patient:read','patient:write'])] 
    private Collection $appointments;

    public function __construct()
    {
        $this->medicalRecord = new ArrayCollection();
        $this->appointments = new ArrayCollection();
    }



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }


    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(string $gender): static
    {
        $this->gender = $gender;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getBloodType(): ?string
    {
        return $this->bloodType;
    }

    public function setBloodType(string $bloodType): static
    {
        $this->bloodType = $bloodType;

        return $this;
    }

    public function getMedicalHistory(): ?string
    {
        return $this->medicalHistory;
    }

    public function setMedicalHistory(string $medicalHistory): static
    {
        $this->medicalHistory = $medicalHistory;

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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

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

    public function getModifiedAt(): ?\DateTimeInterface
    {
        return $this->modified_at;
    }

    public function setModifiedAt(\DateTimeInterface $modified_at): static
    {
        $this->modified_at = $modified_at;

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

    public function getBirthDate(): ?\DateTimeInterface
    {
        return $this->birthDate;
    }

    public function setBirthDate(\DateTimeInterface $birthDate): static
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    public function getCni(): ?string
    {
        return $this->cni;
    }

    public function setCni(string $cni): static
    {
        $this->cni = $cni;

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
            $medicalRecord->setPatient($this);
        }

        return $this;
    }

    public function removeMedicalRecord(MedicalRecord $medicalRecord): static
    {
        if ($this->medicalRecord->removeElement($medicalRecord)) {
            // set the owning side to null (unless already changed)
            if ($medicalRecord->getPatient() === $this) {
                $medicalRecord->setPatient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Appointment>
     */
    public function getAppointments(): Collection
    {
        return $this->appointments;
    }

    public function addAppointment(Appointment $appointment): static
    {
        if (!$this->appointments->contains($appointment)) {
            $this->appointments->add($appointment);
            $appointment->setPatient($this);
        }

        return $this;
    }

    public function removeAppointment(Appointment $appointment): static
    {
        if ($this->appointments->removeElement($appointment)) {
            // set the owning side to null (unless already changed)
            if ($appointment->getPatient() === $this) {
                $appointment->setPatient(null);
            }
        }

        return $this;
    }
 
}
