<?php

namespace DentalOffice\InvoiceBundle\Domain\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use DentalOffice\InvoiceBundle\Domain\Repository\InvoiceRepository;
use DentalOffice\InvoiceBundle\Infrastructure\Persistence\Doctrine\Provider\State\InvoicesGetCollectionProvider;
use DentalOffice\MedicalRecordBundle\Domain\Entity\MedicalRecord;
use DentalOffice\PaymentsBundle\Domain\Entity\Payment;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Metadata\Get;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: InvoiceRepository::class)]
#[ApiResource(
    order: ['id' => 'DESC'],
    operations:[
            new GetCollection(
                security: "is_granted('ROLE_ADMIN')",
                uriTemplate: "get/invoices/by/pagination",
                normalizationContext:['groups' => 'invoice:write'],
                denormalizationContext: ['groups' => 'invoice:read'],
                provider: InvoicesGetCollectionProvider::class
            ),
            new Get(
                security: "is_granted('ROLE_ADMIN')",
                uriTemplate: "get/invoice/{id}",
                normalizationContext:['groups' => 'invoice:write'],
                denormalizationContext: ['groups' => 'invoice:read'],
            ),
            new Get(
                security: "is_granted('ROLE_ADMIN')",
                uriTemplate: "get/invoice/{id}",
                normalizationContext:['groups' => 'invoice:write'],
                denormalizationContext: ['groups' => 'invoice:read'],
            ),

        ],
        
        paginationPartial: false,

)]
#[ApiFilter(SearchFilter::class, properties: [
    'invoiceNumber' => 'exact', 
   
])]
class Invoice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['patient:read','patient:write','invoice:write','invoice:read','medical_record:read','medical_record:write'])]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: false)]
    #[Groups(['patient:read','patient:write','invoice:write','invoice:read','medical_record:read','medical_record:write'])]
    private ?string $invoiceNumber = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['patient:read','patient:write','invoice:write','invoice:read','medical_record:read','medical_record:write'])]
    private ?\DateTimeInterface $invoiceDate = null;

    #[ORM\Column]
    #[Groups(['patient:read','patient:write','invoice:write','invoice:read','medical_record:read','medical_record:write'])]
    private ?float $totalAmount = null;

    #[ORM\Column]
    #[Groups(['patient:read','patient:write','invoice:write','invoice:read','medical_record:read','medical_record:write'])]
    private ?float $totalPaid = null;

    #[ORM\Column]
    #[Groups(['patient:read','patient:write','invoice:write','invoice:read','medical_record:read','medical_record:write'])]
    private ?float $remainingDue = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'payments')]
    #[Groups(['patient:read','patient:write','invoice:write','invoice:read','medical_record:read','medical_record:write'])]
    private ?self $invoice = null;

    #[ORM\OneToMany(mappedBy: 'invoice', targetEntity: Payment::class, cascade: ['persist'])]
    #[Groups(['patient:read','patient:write','invoice:write','invoice:read','medical_record:read','medical_record:write'])]
    #[ORM\OrderBy(['id'=>'DESC'])]
    private Collection $payments;

    #[ORM\ManyToOne(inversedBy: 'invoice')]
    #[Groups('invoice:write','invoice:read')]
    private ?MedicalRecord $medicalRecord = null;

    public function __construct()
    {
         $this->payments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInvoiceNumber(): ?string
    {
        return $this->invoiceNumber;
    }

    public function setInvoiceNumber(string $invoiceNumber): static
    {
        $this->invoiceNumber = $invoiceNumber;

        return $this;
    }

    public function generateInvoiceNumber(): string
    {
        $prefix = 'FAC';
        $date = $this->invoiceDate->format('Ymd');
        $rand = str_pad((string)random_int(1, 999), 3, '0', STR_PAD_LEFT);

        return  sprintf('%s-%s-%s', $prefix, $date, $rand);
    }
    


    public function getInvoiceDate(): ?\DateTimeInterface
    {
        return $this->invoiceDate;
    }

    public function setInvoiceDate(\DateTimeInterface $invoiceDate): static
    {
        $this->invoiceDate = $invoiceDate;

        return $this;
    }

    public function getTotalAmount(): ?float
    {
        return $this->totalAmount;
    }

    public function setTotalAmount(float $totalAmount): static
    {
        $this->totalAmount = $totalAmount;

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

    public function getInvoice(): ?self
    {
        return $this->invoice;
    }

    public function setInvoice(?self $invoice): static
    {
        $this->invoice = $invoice;

        return $this;
    }

    public function addPayment(Payment $payment): static
    {
        if (!$this->payments->contains($payment)) {
            $this->payments[] = $payment;
            $payment->setInvoice($this); // Set inverse side
        }

        return $this;
    }

    public function getPayments(): Collection
    {
        return $this->payments;
    }

    public function getMedicalRecord(): ?MedicalRecord
    {
        return $this->medicalRecord;
    }

    public function setMedicalRecord(?MedicalRecord $medicalRecord): static
    {
        $this->medicalRecord = $medicalRecord;

        return $this;
    }
   
}
