<?php

namespace DentalOffice\PaymentsBundle\Infrastructure\Persistence\Doctrine\Entity;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Entity\VisitOrmEntity;
use DentalOffice\PaymentsBundle\Infrastructure\Persistence\Doctrine\Repository\PaymentOrmEntityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Table(
    name: "payment",
    indexes: [
       
    ]
)]
#[ORM\Entity(repositoryClass: PaymentOrmEntityRepository::class)]
#[ApiResource(
    operations:[
            new GetCollection(
            normalizationContext: ['groups'=>'payment:write'],
            denormalizationContext: ['groups'=>'payment:read'],
            paginationClientItemsPerPage: true,
            paginationItemsPerPage: true,
        ),
    ]
)]
class PaymentOrmEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['patient:read','patient:write','payment:write', 'payment:read','medical_record:read','medical_record:write','visit:read','visit:write','invoice:write','invoice:read'])]
    private ?int $id = null;



    #[ORM\Column(length: 255)]
    #[Groups(['patient:read','patient:write','payment:write', 'payment:read','medical_record:read','medical_record:write','visit:read','visit:write','invoice:write','invoice:read'])]
    private ?string $method = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['patient:read','patient:write','payment:write', 'payment:read','medical_record:read','medical_record:write','visit:read','visit:write','invoice:write','invoice:read'])]
    private ?\DateTimeInterface $paymentDate = null;


    #[ORM\Column]
    #[Groups(['patient:read','patient:write','payment:write', 'payment:read','medical_record:read','medical_record:write','visit:read','visit:write','invoice:write','invoice:read'])]
    private ?float $amountPaid = null;

    #[ORM\OneToOne(cascade: ['persist'])]
    private ?VisitOrmEntity $visit = null;


    public function getId(): ?int
    {
        return $this->id;
    }


    public function getMethod(): ?string
    {
        return $this->method;
    }

    public function setMethod(string $method): static
    {
        $this->method = $method;

        return $this;
    }

    public function getPaymentDate(): ?\DateTimeInterface
    {
        return $this->paymentDate;
    }

    public function setPaymentDate(\DateTimeInterface $paymentDate): static
    {
        $this->paymentDate = $paymentDate;

        return $this;
    }

    public function getAmountPaid(): ?float
    {
        return $this->amountPaid;
    }

    public function setAmountPaid(float $amountPaid): static
    {
        $this->amountPaid = $amountPaid;

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


}
