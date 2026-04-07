<?php

namespace DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Put;
use DentalOffice\AppointmentSchedulingBundle\Domain\Exception\PatientNotFoundException;
use DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Provider\State\AppointmentsGetCollectionProvider;
use DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Provider\State\AppointmentStateProvider;
use DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Repository\AppointmentRepository as RepositoryAppointmentRepository;
use DentalOffice\AppointmentSchedulingBundle\Presentation\Api\Processor\State\AppointmentCompletedProcessor;
use DentalOffice\AppointmentSchedulingBundle\Presentation\Api\Processor\State\AppointmentConfirmProcessor;
use DentalOffice\AppointmentSchedulingBundle\Presentation\Api\Processor\State\AppointmentPutProcessor as StateAppointmentPutProcessor;
use DentalOffice\AppointmentSchedulingBundle\Presentation\Api\Processor\State\AppointmentScheduledProcessor;
use DentalOffice\PatientBundle\Domain\Entity\Patient;
use DentalOffice\UserBundle\Domain\Entity\User;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: RepositoryAppointmentRepository::class)]
#[ORM\Table(
    name: "appointment",
    indexes: [
        new ORM\Index(name: "idx_practitioner_start", columns: ["user_id", "start"]),
        new ORM\Index(name: "idx_practitioner_start_end", columns: ["user_id", "start", "end"]),
        new ORM\Index(name: "idx_patient", columns: ["patient_id"]),
        new ORM\Index(name: "idx_status", columns: ["status"])
    ]
)]
#[ApiResource(
    order: ['id' => 'DESC'],

    operations:[
        new Post(
            security: "is_granted('ROLE_OWNER')",
            uriTemplate: "/create/patient/{patientId}/appointment",
            uriVariables: [
                'patientId' => new Link(
                    fromClass: Patient::class,
                    toProperty: 'patient'
                ),
            ],
            provider: AppointmentStateProvider::class,
            processor: AppointmentScheduledProcessor::class,
            normalizationContext: ['groups'=>'appointment:write'],
            denormalizationContext: ['groups'=>'appointment:read'],
        ),
        new Post(
            securityPostDenormalize: "is_granted('APPOINTMENT_COMPLETE', object)",
            uriTemplate: "/complete/appointment/{appointmentId}",
            uriVariables: [
                'appointmentId' => new Link(
                    fromClass: AppointmentOrmEntity::class,
                   
                )
            ],
            processor: AppointmentCompletedProcessor::class,
            normalizationContext: ['groups'=>'appointment:write'],
            denormalizationContext: ['groups'=>'appointment:read'],
        ),
        new Patch(
            security:  "is_granted('ROLE_OWNER')",
            uriTemplate: "/confirme/appointment/{appointmentId}",
            uriVariables: [
                'appointmentId' => new Link(
                    fromClass: AppointmentOrmEntity::class,
                   
                )
            ],
            processor: AppointmentConfirmProcessor::class,
            normalizationContext: ['groups'=>'appointment:write'],
            denormalizationContext: ['groups'=>'appointment:read'],
           
        ),
        new Get(
            security: "is_granted('ROLE_OWNER')",
            uriTemplate: "/get/appointment/by/{id}",
            normalizationContext: ['groups'=>'appointment:write'],
            denormalizationContext: ['groups'=>'appointment:read']
        ),
        new Put(
            security: "is_granted('ROLE_OWNER')",
            uriTemplate: "/update/appointment/{id}",
            processor: StateAppointmentPutProcessor::class,
            normalizationContext: ['groups'=>'appointment:write'],
            denormalizationContext: ['groups'=>'appointment:read'],
        ),
        new GetCollection(
            security: "is_granted('ROLE_OWNER')",
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

class  AppointmentOrmEntity
{
   #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['appointment:write','appointment:read','patient:read','patient:write'])]
    private ?int $id = null;



    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['appointment:write','appointment:read','patient:read','patient:write','medical_record:read','medical_record:write'])]
    private ?\DateTimeInterface $modifiedAt = null;

    #[ORM\Column(length: 255)]
    #[Groups(['appointment:write','appointment:read','patient:read','patient:write','medical_record:read','medical_record:write'])]
    private ?string $reason = null;

    #[ORM\ManyToOne(inversedBy: 'appointment', cascade: ['persist'])]
    #[Groups(['appointment:write','appointment:read','patient:read','patient:write','medical_record:read','medical_record:write'])]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'appointments', cascade: ['persist'])]
    #[Groups(['appointment:write','appointment:read','patient:read','patient:write','medical_record:read','medical_record:write'])]
    private ?User $createdBy = null;

    #[ORM\ManyToOne(inversedBy: 'appointments', cascade: ['persist'])]
    #[Groups(['appointment:write','appointment:read','patient:read','patient:write','medical_record:read','medical_record:write'])]
    private ?User $modifiedBy = null;

    #[ORM\Column]
    #[Groups(['appointment:write','appointment:read','patient:read','patient:write','medical_record:read','medical_record:write'])]
    private ?string $status = '';

    #[ORM\Column]
    #[Groups(['appointment:write','appointment:read','patient:read','patient:write','medical_record:read','medical_record:write'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'appointments', cascade: ['persist'])]
    #[Groups(['appointment:write','appointment:read','patient:read','patient:write','medical_record:read','medical_record:write'])]
    private ?Patient $patient = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $start = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $end = null;



   
    public function __construct()
    {
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
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

    public function getStart(): ?\DateTimeImmutable
    {
        return $this->start;
    }

    public function setStart(\DateTimeImmutable $start): static
    {
        $this->start = $start;

        return $this;
    }

    public function getEnd(): ?\DateTimeImmutable
    {
        return $this->end;
    }

    public function setEnd(\DateTimeImmutable $end): static
    {
        $this->end = $end;

        return $this;
    }





}