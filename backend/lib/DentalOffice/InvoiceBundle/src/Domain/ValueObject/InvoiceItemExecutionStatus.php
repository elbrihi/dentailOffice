<?php

namespace DentalOffice\InvoiceBundle\Domain\ValueObject;

use DentalOffice\InvoiceBundle\Domain\Exception\InvalidInvoiceItemStatus;

final class InvoiceItemExecutionStatus
{

    // 🎨 --- CORE STATES ---
    public const PLANNED         = 'planned';
    public const IN_PROGRESS     = 'in_progress';
    public const COMPLETED       = 'completed';
    public const CANCELLED       = 'cancelled';

    private const ALL = [
        self::PLANNED,
        self::IN_PROGRESS,
        self::COMPLETED,
        self::CANCELLED,
    ];

    private string $value;

    private function __construct(string $value)
    {
        if (!in_array($value, self::ALL, true)) {

            throw InvalidInvoiceItemStatus::invalidStatus($value);
        }

        $this->value = $value;
    }

    public static function handleInvoiceItemExecutionStatus(
      string $value
    ):self {
        if (!in_array($value, self::ALL, true)) {

            throw InvalidInvoiceItemStatus::invalidStatus($value);
        }
        return new self($value);
      
    } 

    // 🏗️ --- NAMED CONSTRUCTORS ---

    public static function planned(): self
    {
        return new self(self::PLANNED);
    }
    public static function inProgress(string $value = self::PLANNED): self
    {
        if ($value !== self::PLANNED) {
            throw InvalidInvoiceItemStatus::invalidTransition(self::PLANNED, $value);
        }

        $value = self::IN_PROGRESS;

        
        return new self($value);
    }
    public static function completed(string $value = self::IN_PROGRESS): self
    {
        if ($value !== self::IN_PROGRESS) {
            throw InvalidInvoiceItemStatus::invalidTransition(self::IN_PROGRESS, $value);
        }
        $value = self::COMPLETED;
        return new self($value);
    }


    public static function cancelled(string $value =''): self
    {
        if(!in_array($value, [self::IN_PROGRESS,self::COMPLETED,self::PLANNED])){
         
           throw InvalidInvoiceItemStatus::invalidTransition(self::PLANNED, $value);
        }
       
        $value = self::CANCELLED;
        return new self($value);
    }

    // 🧠 --- STATE CHECKS ---

    public function isPlanned(): bool
    {
        return $this->value === self::PLANNED;
    }
    public function isInProgress(): bool
    {
        return $this->value === self::IN_PROGRESS;
    }
    public function isCompleted(): bool
    {
        return $this->value === self::COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->value === self::CANCELLED;
    }

    // 🔄 --- TRANSITIONS ---

    public function markInProgress(): self
    {
        $this->guard([self::PLANNED], self::IN_PROGRESS);
        return self::inProgress();
    }

    public function markCompleted(): self
    {
        $this->guard([self::IN_PROGRESS], self::COMPLETED);
        return self::completed();
    }



    public function cancel(): self
    {
        // ❗ real SaaS rule:
        // cannot cancel if money already involved
        $this->guard(
            [self::PLANNED, self::IN_PROGRESS, self::COMPLETED],
            self::CANCELLED
        );

        return self::cancelled();
    }

    // 🛡️ --- TRANSITION GUARD ---

    private function guard(array $allowed, string $target): void
    {
        if (!in_array($this->value, $allowed, true)) {
            //  throw InvalidInvoiceItemStatus::invalidTransition($this->value, $target);
        }
    }

    // 🎯 --- VALUE ACCESS ---

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

}
