<?php

namespace DentalOffice\InvoiceBundle\Domain\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use DentalOffice\InvoiceBundle\Domain\Repository\InvoiceRepository;
use DentalOffice\InvoiceBundle\Infrastructure\Persistence\Doctrine\Provider\State\InvoicesGetCollectionProvider;
use DentalOffice\MedicalRecordBundle\Domain\Entity\MedicalRecord;
use DentalOffice\PaymentsBundle\Domain\Entity\Payment;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
            )
        ],
        paginationPartial: false,

)]
class Invoice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['invoice:write','invoice:read','medical_record:read','medical_record:write'])]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: false)]
    #[Groups(['invoice:write','invoice:read','medical_record:read','medical_record:write'])]
    private ?string $invoiceNumber = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['invoice:write','invoice:read','medical_record:read','medical_record:write'])]
    private ?\DateTimeInterface $invoiceDate = null;

    #[ORM\Column]
    #[Groups(['invoice:write','invoice:read','medical_record:read','medical_record:write'])]
    private ?float $totalAmount = null;

    #[ORM\Column]
    #[Groups(['invoice:write','invoice:read','medical_record:read','medical_record:write'])]
    private ?float $totalPaid = null;

    #[ORM\Column]
    #[Groups(['invoice:write','invoice:read','medical_record:read','medical_record:write'])]
    private ?float $remainingDue = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'payments')]
    #[Groups(['invoice:write','invoice:read','medical_record:read','medical_record:write'])]
    private ?self $invoice = null;

    #[ORM\OneToMany(mappedBy: 'invoice', targetEntity: Payment::class, cascade: ['persist'])]
    #[Groups(['invoice:write','invoice:read','medical_record:read','medical_record:write'])]
    private Collection $payments;

    #[ORM\ManyToOne(inversedBy: 'invoice')]
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
