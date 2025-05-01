<?php

namespace DentalOffice\MedicalRecordBundle\Domain\Entity;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use DentalOffice\MedicalRecordBundle\Domain\Repository\MedicalRecordRepository;
use DentalOffice\MedicalRecordBundle\Infrastructure\Persistence\Doctrine\Processor\State\MedicalRecordPostProcessor;
use DentalOffice\MedicalRecordBundle\Infrastructure\Persistence\Doctrine\Processor\State\MedicalRecordPutProcessor;
use DentalOffice\MedicalRecordBundle\Infrastructure\Persistence\Doctrine\Provider\State\GetMedicalRecordByPatient;
use DentalOffice\MedicalRecordBundle\Infrastructure\Persistence\Doctrine\Provider\State\GetMedicalRecordByPatientProvider;
use DentalOffice\MedicalRecordBundle\Infrastructure\Persistence\Doctrine\Provider\State\GetPatientByMedicalRecord;
use DentalOffice\MedicalRecordBundle\Infrastructure\Persistence\Doctrine\Provider\State\MedicalRecordCollectionProvider;
use DentalOffice\MedicalRecordBundle\Infrastructure\Persistence\Doctrine\Provider\State\PostMedicalRecordProvider;
use DentalOffice\PatientBundle\Domain\Entity\Patient;
use DentalOffice\UserBundle\Domain\Entity\User;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: MedicalRecordRepository::class)]
#[ApiResource(
    order: ['id' => 'DESC'],
    operations:[
            new Post(
                security: "is_granted('ROLE_ADMIN')",
                uriTemplate: "/create/patient/{patientId}/medicalRecords",
                uriVariables: [
                    'patientId' => new Link(
                        fromClass: Patient::class,
                        toProperty: 'patient'
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

class MedicalRecord
{
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['medical_record:read','medical_record:write', 'patient:read','patient:write'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['medical_record:read','medical_record:write', 'patient:read','patient:write'])]
    private ?\DateTimeInterface $visit_date = null;

    #[ORM\Column(length: 255)]
    #[Groups(['medical_record:read','medical_record:write', 'patient:read','patient:write'])]    
    private ?string $chief_complaint = null;

    #[ORM\Column(length: 255)]
    #[Groups(['medical_record:read','medical_record:write', 'patient:read','patient:write'])]
    private ?string $clinical_diagnosis = null;

    #[ORM\Column(length: 255)]
    #[Groups(['medical_record:read','medical_record:write', 'patient:read','patient:write'])]
    private ?string $treatment_plan = null;


    #[ORM\Column(type: Types::ARRAY)]
    #[Groups(['medical_record:read','medical_record:write', 'patient:read','patient:write'])]
    private array $prescriptions = [];


    #[Groups(['medical_record:read','medical_record:write', 'patient:read','patient:write'])]
    #[ORM\Column(length: 255)]
    private ?string $notes = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['medical_record:read','medical_record:write', 'patient:read','patient:write'])]
    private ?\DateTimeInterface $follow_up_date = null;

    #[ORM\ManyToOne(inversedBy: 'medicalRecord')]
    #[Groups(['medical_record:read','medical_record:write'])]
    private ?Patient $patient = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['medical_record:read','medical_record:write', 'patient:read','patient:write'])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'medicalRecords')]
    #[Groups(['medical_record:read','medical_record:write', 'patient:read','patient:write'])]
    private ?User $createdBy = null;

    #[ORM\ManyToOne(inversedBy: 'medicalRecordsModifier')]
    #[Groups(['medical_record:read','medical_record:write', 'patient:read','patient:write'])]
    private ?User $modifiedBy = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['medical_record:read','medical_record:write', 'patient:read','patient:write'])]
    private ?\DateTimeInterface $modifiedAt = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVisitDate(): ?\DateTimeInterface
    {
        return $this->visit_date;
    }

    public function setVisitDate(\DateTimeInterface $visit_date): static
    {
        $this->visit_date = $visit_date;

        return $this;
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

    public function getPrescriptions(): array
    {
        return $this->prescriptions;
    }

    public function setPrescriptions(array $prescriptions): static
    {
        $this->prescriptions = $prescriptions;

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

    public function getFollowUpDate(): ?\DateTimeInterface
    {
        return $this->follow_up_date;
    }

    public function setFollowUpDate(\DateTimeInterface $follow_up_date): static
    {
        $this->follow_up_date = $follow_up_date;

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

}
