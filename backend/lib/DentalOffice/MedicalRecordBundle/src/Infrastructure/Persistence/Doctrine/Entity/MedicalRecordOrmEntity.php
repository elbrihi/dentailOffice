<?php

namespace DentalOffice\MedicalRecordBundle\Infrastructure\Persistence\Doctrine\Entity;


use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Entity\AppointmentOrmEntity;
use DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Entity\VisitOrmEntity;
use DentalOffice\InvoiceBundle\Domain\Entity\Invoice;
use DentalOffice\InvoiceBundle\Infrastructure\Persistence\Doctrine\Entity\InvoiceOrmEntity;
use DentalOffice\MedicalRecordBundle\Infrastructure\Persistence\Doctrine\Processor\State\MedicalRecordPostProcessor;
use DentalOffice\MedicalRecordBundle\Infrastructure\Persistence\Doctrine\Processor\State\MedicalRecordPutProcessor;
use DentalOffice\MedicalRecordBundle\Infrastructure\Persistence\Doctrine\Provider\State\MedicalRecordCollectionProvider;
use DentalOffice\MedicalRecordBundle\Infrastructure\Persistence\Doctrine\Provider\State\PostMedicalRecordProvider;
use DentalOffice\MedicalRecordBundle\Infrastructure\Persistence\Doctrine\Repository\MedicalRecordRepository;
use DentalOffice\PatientBundle\Domain\Entity\Patient;
use DentalOffice\UserBundle\Domain\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

#[ORM\Entity(repositoryClass: MedicalRecordRepository::class)]
#[ORM\Table(
    name: "medical_record",
    indexes: [
        new ORM\Index(name: "idx_createdat", columns: ["createdAt"]),
    ]
)]

// medicalrecord
#[ApiResource(
    order: ['id' => 'DESC'],
    operations:[
            new Post(
                security: "is_granted('ROLE_ADMIN')",
                uriTemplate: "/create/patient/{patientId}/appointment/{appointmentId}/medicalRecords",
                uriVariables: [
                    'patientId' => new Link(
                        fromClass: Patient::class,
                        toProperty: 'patient'
                    ),
                    'appointmentId' => new Link(
                        fromClass: AppointmentOrmEntity::class,
                        toProperty: 'appointment'
                    ),
                ],
                processor: MedicalRecordPostProcessor::class,
                provider: PostMedicalRecordProvider::class,
                normalizationContext: ['groups' => ['medical_record:read']],
                denormalizationContext: ['groups' => ['medical_record:write']],
            ),
            new Put(
                security: "is_granted('ROLE_ADMIN')",
                uriTemplate: "/update/medicalRecords/{id}",
                processor: MedicalRecordPutProcessor::class,

            ),
            new Get(
                uriTemplate: "/get/medicalRecord/{id}",
                normalizationContext: ['groups' => ['medical_record:read']],
                denormalizationContext: ['groups' => ['medical_record:read']],
            ),
            new GetCollection(
                uriTemplate: "get/medicalRecord/by/pagination",
                normalizationContext: ['groups' => ['medical_record:read']],
                denormalizationContext: ['groups' => ['medical_record:read']],
                paginationClientItemsPerPage: true,
                paginationItemsPerPage: true,
                provider: MedicalRecordCollectionProvider::class,
            ),
            new GetCollection(
                uriTemplate: "get/medicalrecords",
                
                normalizationContext: ['groups' => ['medical_record:read']],
                denormalizationContext: ['groups' => ['medical_record:read']],
              
            ),
            

        ],
        paginationPartial: false,
)]
#[ApiFilter(SearchFilter::class, properties: [
    'chief_complaint' => 'partial',
])]

class MedicalRecordOrmEntity
{
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['medical_record:read','medical_record:write', 'patient:read','patient:write','visit:read','visit:write','payment:write', 'payment:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['medical_record:read','medical_record:write', 'patient:read','patient:write','visit:read','visit:write'])]
    private ?string $chief_complaint = null;

    #[ORM\Column(length: 255)]
    #[Groups(['medical_record:read','medical_record:write', 'patient:read','patient:write','visit:read','visit:write'])]
    private ?string $clinical_diagnosis = null;

    #[ORM\Column(length: 255)]
    #[Groups(['medical_record:read','medical_record:write', 'patient:read','patient:write','visit:read','visit:write'])]
    private ?string $treatment_plan = null;

    #[Groups(['medical_record:read','medical_record:write', 'patient:read','patient:write','visit:read','visit:write'])]
    #[ORM\Column(length: 255)]
    private ?string $notes = null;

    #[ORM\ManyToOne(inversedBy: 'medicalRecord')]
    #[Groups(['medical_record:read','medical_record:write', 'patient:read','patient:write','visit:read','visit:write','invoice:write','invoice:read'])]
    private ?Patient $patient = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['medical_record:read','medical_record:write', 'patient:read','patient:write','visit:read','visit:write'])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'medicalRecords')]
    #[Groups(['medical_record:read','medical_record:write', 'patient:read','patient:write','visit:read','visit:write'])]
    private ?User $createdBy = null;

    #[ORM\ManyToOne(inversedBy: 'medicalRecordsModifier')]
    #[Groups(['medical_record:read','medical_record:write', 'patient:read','patient:write','visit:read','visit:write'])]
    private ?User $modifiedBy = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['medical_record:read','medical_record:write', 'patient:read','patient:write','visit:read','visit:write'])]
    private ?\DateTimeInterface $modifiedAt = null;

    #[ORM\Column]
    #[Groups(['medical_record:read','medical_record:write', 'patient:read','patient:write','visit:read','visit:write'])]
    private ?float $agreedAmount = null;

    #[ORM\Column]
    #[Groups(['medical_record:read','medical_record:write', 'patient:read','patient:write','visit:read','visit:write'])]
    private ?float $totalPaid = null;

    #[ORM\Column]
    #[Groups(['medical_record:read','medical_record:write', 'patient:read','patient:write','visit:read','visit:write'])]
    private ?float $remainingDue = null;


    #[ORM\OneToMany(targetEntity: VisitOrmEntity::class, mappedBy: 'medicalRecord' , cascade: ['persist'])]
    #[Groups(['medical_record:read','medical_record:write','patient:read','patient:write'])]
    #[ORM\OrderBy(['id' => 'DESC'])]
    #[MaxDepth(1)]
    private Collection $visits;

    #[ORM\OneToMany(targetEntity: InvoiceOrmEntity::class, mappedBy: 'medicalRecord', cascade: ['persist'])]
    #[Groups(['patient:read','patient:write','medical_record:read','medical_record:write'])]
    private Collection $invoice;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'prescriptions')]
    private ?self $medicalRecordOrmEntity = null;

    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'medicalRecordOrmEntity')]
    private Collection $prescriptions;

    #[ORM\ManyToOne(inversedBy: 'medicalRecord' , cascade: ['persist'])]
    private ?User $user = null;


    public function __construct()
    {
        $this->visits = new ArrayCollection();
        $this->invoice = new ArrayCollection();
        $this->prescriptions = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getChiefComplaint(): ?string
    {
        return $this->chief_complaint;
    }

    public function setChiefComplaint(string $chief_complaint): static
    {
        $this->chief_complaint = $chief_complaint;

        return $this;
    }

    public function getClinicalDiagnosis(): ?string
    {
        return $this->clinical_diagnosis;
    }

    public function setClinicalDiagnosis(string $clinical_diagnosis): static
    {
        $this->clinical_diagnosis = $clinical_diagnosis;

        return $this;
    }

    public function getTreatmentPlan(): ?string
    {
        return $this->treatment_plan;
    }

    public function setTreatmentPlan(string $treatment_plan): static
    {
        $this->treatment_plan = $treatment_plan;

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

    public function getPatient(): ?Patient
    {
        return $this->patient;
    }

    public function setPatient(?Patient $patient): static
    {
        $this->patient = $patient;

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
        return $this->modifiedAt;
    }

    public function setModifiedAt(\DateTimeInterface $modifiedAt): static
    {
        $this->modifiedAt = $modifiedAt;

        return $this;
    }

    public function getAgreedAmount(): ?float
    {
        return $this->agreedAmount;
    }

    public function setAgreedAmount(float $agreedAmount): static
    {
        $this->agreedAmount = $agreedAmount;

        return $this;
    }

    public function getTotalPaid(): ?float
    {
        return $this->totalPaid;
    }

    public function setTotalPaid(float $totalPaid): static
    {
        $this->totalPaid = $totalPaid;

        return $this;
    }

    public function getRemainingDue(): ?float
    {
        return $this->remainingDue;
    }

    public function setRemainingDue(float $remainingDue): static
    {
        $this->remainingDue = $remainingDue;

        return $this;
    }


    /**
     * @return Collection<int, Visit>
     */
    public function getVisits(): Collection
    {
        return $this->visits;
    }



    public function addVisit(VisitOrmEntity $visit): static
    {
        if (!$this->visits->contains($visit)) {
            $this->visits->add($visit);
            $visit->setMedicalRecord($this);
        }

        return $this;
    }

    public function removeVisit(VisitOrmEntity $visit): static
    {
        if ($this->visits->removeElement($visit)) {
            // set the owning side to null (unless already changed)
            if ($visit->getMedicalRecord() === $this) {
                $visit->setMedicalRecord(null);
            }
        }

        return $this;
    }


    public function getInvoice(): Collection
    {
        return $this->invoice;
    }

    public function addInvoice(InvoiceOrmEntity $invoice): static
    {
        if (!$this->invoice->contains($invoice)) {
            $this->invoice->add($invoice);
            $invoice->setMedicalRecord($this);
        }

        return $this;
    }

    public function removeInvoice(InvoiceOrmEntity $invoice): static
    {
        if ($this->invoice->removeElement($invoice)) {
            // set the owning side to null (unless already changed)
            if ($invoice->getMedicalRecord() === $this) {
                $invoice->setMedicalRecord(null);
            }
        }

        return $this;
    }

    public function getMedicalRecordOrmEntity(): ?self
    {
        return $this->medicalRecordOrmEntity;
    }

    public function setMedicalRecordOrmEntity(?self $medicalRecordOrmEntity): static
    {
        $this->medicalRecordOrmEntity = $medicalRecordOrmEntity;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getPrescriptions(): Collection
    {
        return $this->prescriptions;
    }

    public function addPrescription(self $prescription): static
    {
        if (!$this->prescriptions->contains($prescription)) {
            $this->prescriptions->add($prescription);
            $prescription->setMedicalRecordOrmEntity($this);
        }

        return $this;
    }

    public function removePrescription(self $prescription): static
    {
        if ($this->prescriptions->removeElement($prescription)) {
            // set the owning side to null (unless already changed)
            if ($prescription->getMedicalRecordOrmEntity() === $this) {
                $prescription->setMedicalRecordOrmEntity(null);
            }
        }

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






}
