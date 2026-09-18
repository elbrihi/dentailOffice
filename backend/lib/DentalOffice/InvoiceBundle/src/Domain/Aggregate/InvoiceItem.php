<?php

namespace DentalOffice\InvoiceBundle\Domain\Aggregate;

use DentalOffice\InvoiceBundle\Domain\Exception\InvalidDoneInvoiceItemException;
use DentalOffice\InvoiceBundle\Domain\Exception\InvalidInProgressInvoiceItemException;
use DentalOffice\InvoiceBundle\Domain\Exception\InvalidPartiallyPaidInvoiceItemException;
use DentalOffice\InvoiceBundle\Domain\ValueObject\InvoiceItemExecutionStatus;
use DentalOffice\InvoiceBundle\Domain\ValueObject\InvoiceItemPaymentStatus;
use DentalOffice\InvoiceBundle\Domain\ValueObject\InvoiceItemStatus;
use DentalOffice\MedicalRecordBundle\Domain\Exception\InvalidInProgressException;

final class InvoiceItem
{



    private int $id;
    private string $description;
    private float $amount;
    private ?int $visitId;
    private float $remainingItemAmount;
    private array $executionHistory;
    private array $paymentHistory;
    private InvoiceItemStatus $invoiceItemStatus; // execution_status and payment_status
    private InvoiceItemExecutionStatus $executionStatus;
    private InvoiceItemPaymentStatus $paymentStatus;
    private function __construct(
        int $id,
        string $description,
        float $amount,
        ?int $visitId = null,
        ?InvoiceItemStatus $invoiceItemStatus,
        ?InvoiceItemExecutionStatus $executionStatus = null,
        ?InvoiceItemPaymentStatus $paymentStatus = null
    ) {


        if ($amount <= 0) {
            throw new \InvalidArgumentException('Amount must be positive.');
        }

        $this->id = $id;
        $this->description = trim($description);
        $this->amount = $amount;
        $this->visitId = $visitId;
        $this->invoiceItemStatus = $invoiceItemStatus ?? InvoiceItemStatus::planned();
        $this->executionStatus = $executionStatus ?? InvoiceItemExecutionStatus::planned();
        $this->paymentStatus = $paymentStatus ?? InvoiceItemPaymentStatus::unbilled();

       // self::handleInvoiceItemStatus($this);
        // dd((string) $this->executionStatus === InvoiceItemExecutionStatus::COMPLETED
        //     && (string) $this->paymentStatus === InvoiceItemPaymentStatus::UNBILLED);
        

        //dd((string)  $this->executionStatus, (string) $this->paymentStatus );

    }

    public static function handleInvoiceItemStatus(
    InvoiceItem $invoiceItem
    ): void {
        $executionStatus = (string) $invoiceItem->executionStatus;
        $paymentStatus = (string) $invoiceItem->paymentStatus;
        $currentStatus = (string) $invoiceItem->invoiceItemStatus;


        /*
        * ============================================================
        * PLANNED
        * ============================================================
        */
        if (
            $executionStatus === InvoiceItemExecutionStatus::PLANNED
            && $paymentStatus === InvoiceItemPaymentStatus::UNBILLED
        ) {
            $invoiceItem->invoiceItemStatus =
                InvoiceItemStatus::planned();

            return;
        }


        /*
        * ============================================================
        * IN PROGRESS
        * ============================================================
        */
        if (
            $executionStatus === InvoiceItemExecutionStatus::IN_PROGRESS
            && $paymentStatus === InvoiceItemPaymentStatus::UNBILLED
        ) {
            if ($currentStatus !== InvoiceItemStatus::PLANNED) {
                throw InvalidInProgressInvoiceItemException::invalidTransition(
                    $currentStatus,
                    InvoiceItemStatus::IN_PROGRESS
                );
            }

            
            $invoiceItem->invoiceItemStatus =
                InvoiceItemStatus::inProgress($currentStatus);

            return;
        }


        /*
        * ============================================================
        * COMPLETED
        * ============================================================
        */
        if (
            $executionStatus === InvoiceItemExecutionStatus::COMPLETED
            && $paymentStatus === InvoiceItemPaymentStatus::UNBILLED
        ) {
            if ($currentStatus !== InvoiceItemStatus::IN_PROGRESS) {
                throw InvalidInProgressInvoiceItemException::invalidTransition(
                    $currentStatus,
                    InvoiceItemStatus::COMPLETED
                );
            }

            
            $invoiceItem->invoiceItemStatus =
                InvoiceItemStatus::completed($currentStatus);

            return;
        }


        /*
        * ============================================================
        * BILLED
        * ============================================================
        */
        if (
            $executionStatus === InvoiceItemExecutionStatus::COMPLETED
            && $paymentStatus === InvoiceItemPaymentStatus::BILLED
        ) {
            if ($currentStatus !== InvoiceItemStatus::COMPLETED) {
                throw InvalidInProgressInvoiceItemException::invalidTransition(
                    $currentStatus,
                    InvoiceItemStatus::BILLED
                );
            }

            

            $invoiceItem->invoiceItemStatus =
                InvoiceItemStatus::billed($currentStatus);

            return;
        }


        /*
        * ============================================================
        * PARTIALLY PAID
        * ============================================================
        */
        if (
            $executionStatus === InvoiceItemExecutionStatus::COMPLETED
            && $paymentStatus === InvoiceItemPaymentStatus::PARTIALLY_PAID
        ) {
            if (
                !in_array(
                    $currentStatus,
                    [
                        InvoiceItemStatus::BILLED,
                        InvoiceItemStatus::PARTIALLY_PAID,
                    ],
                    true
                )
            ) {
                throw InvalidPartiallyPaidInvoiceItemException::invalidTransition(
                    $currentStatus,
                    InvoiceItemStatus::PARTIALLY_PAID
                );
            }

            $invoiceItem->invoiceItemStatus =
                InvoiceItemStatus::partiallyPaid($currentStatus);

            return;
        }


        /*
        * ============================================================
        * PAID / DONE
        * ============================================================
        */
        if (
            $executionStatus === InvoiceItemExecutionStatus::COMPLETED
            && in_array(
                $paymentStatus,
                [
                    InvoiceItemPaymentStatus::PAID,
                    InvoiceItemPaymentStatus::DONE,
                ],
                true
            )
        ) {
            if (
                !in_array(
                    $currentStatus,
                    [
                        InvoiceItemStatus::BILLED,
                        InvoiceItemStatus::PARTIALLY_PAID,
                        InvoiceItemStatus::PAID,
                    ],
                    true
                )
            ) {
                throw InvalidDoneInvoiceItemException::invalidTransition(
                    $currentStatus,
                    InvoiceItemStatus::DONE
                );
            }

            $invoiceItem->invoiceItemStatus =
                InvoiceItemStatus::done($currentStatus);

            return;
        }


        /*
        * ============================================================
        * OVERPAID
        * ============================================================
        */
        if (
            $executionStatus === InvoiceItemExecutionStatus::COMPLETED
            && $paymentStatus === InvoiceItemPaymentStatus::OVERPAID
        ) {
            if (
                !in_array(
                    $currentStatus,
                    [
                        InvoiceItemStatus::BILLED,
                        InvoiceItemStatus::PAID,
                        InvoiceItemStatus::DONE,
                    ],
                    true
                )
            ) {
                throw InvalidDoneInvoiceItemException::invalidTransition(
                    $currentStatus,
                    InvoiceItemStatus::OVERPAID
                );
            }

            
            $invoiceItem->invoiceItemStatus =
                InvoiceItemStatus::overpaid($currentStatus);

            return;
        }


        /*
        * ============================================================
        * PARTIALLY REFUNDED
        * ============================================================
        */
        if (
            $executionStatus === InvoiceItemExecutionStatus::COMPLETED
            && $paymentStatus === InvoiceItemPaymentStatus::PARTIALLY_REFUNDED
        ) {
            if (
                !in_array(
                    $currentStatus,
                    [
                        InvoiceItemStatus::PARTIALLY_PAID,
                        InvoiceItemStatus::PAID,
                        InvoiceItemStatus::DONE,
                    ],
                    true
                )
            ) {
                throw InvalidDoneInvoiceItemException::invalidTransition(
                    $currentStatus,
                    InvoiceItemStatus::PARTIALLY_REFOUNDED
                );
            }

            $invoiceItem->invoiceItemStatus =
                InvoiceItemStatus::partiallyRefounded($currentStatus);

            return;
        }


        /*
        * ============================================================
        * REFUNDED
        * ============================================================
        */
        if (
            $executionStatus === InvoiceItemExecutionStatus::COMPLETED
            && $paymentStatus === InvoiceItemPaymentStatus::REFUNDED
        ) {
            if (
                !in_array(
                    $currentStatus,
                    [
                        InvoiceItemStatus::PARTIALLY_PAID,
                        InvoiceItemStatus::PAID,
                        InvoiceItemStatus::DONE,
                    ],
                    true
                )
            ) {
                throw InvalidDoneInvoiceItemException::invalidTransition(
                    $currentStatus,
                    InvoiceItemStatus::REFUNDED
                );
            }

            $invoiceItem->invoiceItemStatus =
                InvoiceItemStatus::refunded($currentStatus);

            return;
        }
    }
    public function addExecutionStatus(array $executionHistory): void
    {
        $this->executionHistory[] = $executionHistory;
    }
    // add invoice payment status 
    public function addPaymentStatus(InvoiceItemPaymentStatus $paymentStatus): void
    {
        $this->paymentHistory[] = $paymentStatus;
    }

    public static function invoice(
        int $id,
        string $description,
        float $amount,
        ?int $visitId = null,
        ?InvoiceItemStatus $invoiceItemStatus = null,
        ?InvoiceItemExecutionStatus $executionStatus = null,
        ?InvoiceItemPaymentStatus $paymentStatus = null

    ): self {


        return new self(
            $id,
            $description,
            $amount,
            $visitId,
            $invoiceItemStatus,
            $executionStatus,
            $paymentStatus
        );
    }

    public function  getExecutionStatus(): InvoiceItemExecutionStatus
    {
        return $this->executionStatus;
    }

    public function setPaymentStatus(InvoiceItemPaymentStatus $paymentStatus)
    {
        $this->paymentStatus = $paymentStatus;
    }

    public function getPaymentStatus(): InvoiceItemPaymentStatus
    {
        return $this->paymentStatus;
    }

    /**
     * Get the value of id
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get the value of amount
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * Get the value of description
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Get the value of visitId
     */
    public function getVisitId(): ?int
    {
        return $this->visitId;
    }

    public function __toString()
    {
        return $this->invoiceItemStatus;
    }

    /**
     * Get the value of invoiceItemStatus
     */
    public function getInvoiceItemStatus(): InvoiceItemStatus
    {
        return $this->invoiceItemStatus;
    }
}
