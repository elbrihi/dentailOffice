<?php

namespace DentalOffice\InvoiceBundle\Infrastructure\Persistence\Doctrine\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use DentalOffice\InvoiceBundle\Infrastructure\Persistence\Doctrine\Provider\State\InvoicesGetCollectionProvider;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Metadata\Get;
use DateTimeImmutable;
use DentalOffice\InvoiceBundle\Infrastructure\Persistence\Doctrine\Repository\InvoiceRepository;
use DentalOffice\MedicalRecordBundle\Infrastructure\Persistence\Doctrine\Entity\MedicalRecordOrmEntity;
use DentalOffice\PaymentsBundle\Infrastructure\Persistence\Doctrine\Entity\PaymentOrmEntity;
use Doctrine\DBAL\Types\DateImmutableType;
use Doctrine\DBAL\Types\DateTimeImmutableType;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: InvoiceRepository::class)]
#[ORM\Table(
    name: 'invoice'
)]
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
class InvoiceOrmEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['invoice:read','medical_record:read','patient:read','patient:write'])]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: false)]
    #[Groups(['invoice:read','medical_record:read','patient:read','patient:write'])]
    private ?string $invoiceNumber = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['invoice:read','medical_record:read','patient:read','patient:write'])]
    private ?\DateTimeInterface $invoiceDate = null;

    #[ORM\Column]
    #[Groups(['invoice:read','medical_record:read','patient:read','patient:write'])]
    private ?float $totalAmount = null;

    #[ORM\Column]
    #[Groups(['invoice:read','medical_record:read','patient:read','patient:write'])]
    private ?float $totalPaid = null;

    #[ORM\Column]
    #[Groups(['invoice:read','medical_record:read','patient:read','patient:write'])]
    private ?float $remainingDue = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'payments')]
    #[Groups(['invoice:read','medical_record:read','patient:read','patient:write'])]
    private ?self $invoice = null;

    #[ORM\OneToMany(mappedBy: 'invoice', targetEntity: PaymentOrmEntity::class, cascade: ['persist'])]
    #[ORM\OrderBy(['id'=>'DESC'])]
    private Collection $payments;

    #[ORM\ManyToOne(inversedBy: 'invoice')]
    private ?MedicalRecordOrmEntity $medicalRecord = null;

    #[ORM\OneToMany(targetEntity: InvoiceItemOrmEntity::class, mappedBy: 'invoiceOrmEntity')]
    #[Groups(['invoice:read','medical_record:read','patient:read'])]
    private Collection $invoiceItem;

    #[ORM\Column]
    private ?float $agreedAmount = null;

    public function __construct()
    {
         $this->payments = new ArrayCollection();
         $this->invoiceItem = new ArrayCollection();
          $this->invoiceDate =  new DateTimeImmutable(); 
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
        dd( $invoice);
        $this->invoice = $invoice;

        return $this;
    }

    public function addPayment(PaymentOrmEntity $payment): static
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

    public function getMedicalRecord(): ?MedicalRecordOrmEntity
    {
        return $this->medicalRecord;
    }

    public function setMedicalRecord(?MedicalRecordOrmEntity $medicalRecord): static
    {
        $this->medicalRecord = $medicalRecord;

        return $this;
    }

    /**
     * @return Collection<int, InvoiceItemOrmEntity>
     */
    public function getInvoiceItem(): Collection
    {
        return $this->invoiceItem;
    }

    public function addInvoiceItem(InvoiceItemOrmEntity $invoiceItem): static
    {
        if (!$this->invoiceItem->contains($invoiceItem)) {
            $this->invoiceItem->add($invoiceItem);
            $invoiceItem->setInvoiceOrmEntity($this);
        }

        return $this;
    }

    public function removeInvoiceItem(InvoiceItemOrmEntity $invoiceItem): static
    {
        if ($this->invoiceItem->removeElement($invoiceItem)) {
            // set the owning side to null (unless already changed)
            if ($invoiceItem->getInvoiceOrmEntity() === $this) {
                $invoiceItem->setInvoiceOrmEntity(null);
            }
        }

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
   
}
