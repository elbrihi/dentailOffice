<?php

namespace DentalOffice\AppointmentSchedulingBundle\Domain\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use DentalOffice\AppointmentSchedulingBundle\Domain\Repository\AppointmentRepository;
use DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Processor\State\AppointmentStateProcessor;
use DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Provider\State\AppointmentStateProvider;
use DentalOffice\PatientBundle\Domain\Entity\Patient;
use DentalOffice\UserBundle\Domain\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bundle\SecurityBundle\Security;

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
            processor: AppointmentStateProcessor::class
        ),
    ]
)]
class Appointment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $appointmentDate = null;



    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $modifiedAt = null;

 

    #[ORM\Column(length: 255)]
    private ?string $reason = null;

    #[ORM\ManyToOne(inversedBy: 'appointment', cascade: ['persist'])]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'appointments', cascade: ['persist'])]
    private ?User $createdBy = null;

    #[ORM\ManyToOne(inversedBy: 'appointments', cascade: ['persist'])]
    private ?User $modifiedBy = null;

    #[ORM\Column]
    private ?bool $status = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
 
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
}
