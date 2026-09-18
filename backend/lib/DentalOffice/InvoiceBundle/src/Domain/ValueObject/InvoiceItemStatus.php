<?php

namespace DentalOffice\InvoiceBundle\Domain\ValueObject;

use DentalOffice\InvoiceBundle\Domain\Exception\InvalidDoneInvoiceItemException;
use DentalOffice\InvoiceBundle\Domain\Exception\InvalidInvoiceItemStatus;
use DentalOffice\InvoiceBundle\Domain\Exception\InvalidPartiallyPaidInvoiceItemException;

final class InvoiceItemStatus
{

    // 🎨 --- CORE STATES ---
    public const PLANNED         = 'planned';
    public const IN_PROGRESS     = 'in_progress';
    public const COMPLETED       = 'completed';
    public const BILLED          = 'billed';
    public const UNBILLED         = 'unbilled';
    // 💰 --- FINANCIAL STATES ITEMS ---
    public const PARTIALLY_PAID  = 'partially_paid';
    public const PAID            = 'paid';
    public const REFUNDED        = 'refunded';
    public const PARTIALLY_REFOUNDED = 'partially_refounded';
    public const OVERPAID = 'overpaid';

    // ❌ --- EXCEPTION STATES ---
    public const CANCELLED       = 'cancelled';
    public const DONE = 'done';
    
    // Exceptional 

    public const OVERPAIR = "overpaid";

    private const ALL = [
        self::PLANNED,
        self::IN_PROGRESS,
        self::COMPLETED,
        self::BILLED,
        self::PARTIALLY_PAID,
        self::PAID,
        self::REFUNDED,
        self::CANCELLED,
        self::UNBILLED,
        self::OVERPAIR,
        self::PARTIALLY_REFOUNDED,
        self::DONE
    ];

    private string $value;

    private function __construct(string $value)
    {
       
        if (!in_array($value, self::ALL, true)) {
            throw InvalidInvoiceItemStatus::invalidStatus($value);
        }

       

        $this->value = $value;
         // dd("hi");
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
    public static function unbilled(string $value = self::COMPLETED): self
    {
        if ($value !== self::COMPLETED) {
            throw InvalidInvoiceItemStatus::invalidTransition(self::COMPLETED, $value);
        }
        $value = self::UNBILLED;
        return new self($value);
    }
    public static function billed(string $value = self::COMPLETED): self
    {
        if ($value !== self::COMPLETED) {
            throw InvalidInvoiceItemStatus::invalidTransition(self::COMPLETED, $value);
        }
        
        $value = self::BILLED;
        return new self($value);
    }

    public static function partiallyPaid(string $value = self::BILLED): self
    {
        if ( !in_array($value, [ self::BILLED,self::PARTIALLY_PAID])) {
            throw InvalidInvoiceItemStatus::invalidTransition(self::BILLED, $value);
        }
        $value = self::PARTIALLY_PAID;
        return new self($value);
    }
    public static function paid(string $value = self::PARTIALLY_PAID): self
    {
        if(!in_array($value, [self::BILLED, self::PARTIALLY_PAID])){
            throw InvalidPartiallyPaidInvoiceItemException::invalidTransition($value, self::BILLED );
        }
        
        $value = self::PAID;
        return new self($value);
    }
    public static function refunded(string $value = self::PAID): self
    {
        if(!in_array($value, [self::PAID,self::PARTIALLY_PAID])){
            throw InvalidInvoiceItemStatus::invalidTransition($value, self::PAID, self::PARTIALLY_PAID);
        }
        $value = self::REFUNDED;
        return new self($value);
    }

        public static function partiallyRefounded(string $value = self::PAID): self
    {
        if(!in_array($value, [self::PAID,self::PARTIALLY_PAID])){
            throw InvalidInvoiceItemStatus::invalidTransition($value, self::PAID, self::PARTIALLY_PAID);
        }
        $value = self::PARTIALLY_REFOUNDED;
        return new self($value);
    }

    public static function done(string $status):self
    {
       
      
        
        
        if(!in_array($status,[self::BILLED,self::PAID,self::PARTIALLY_PAID]) )
        {
            
             throw InvalidDoneInvoiceItemException::invalidTransition($status, self::PAID);
        }

         
        $status = self::DONE;
        // done
        return new self($status);

        
    }
    public static function overpaid(string $status):self
    {
       
      
        
    //    if($status !== self::PAID)
    //     {
    //          throw InvalidInvoiceItemStatus::invalidTransition($status, self::PAID);
    //     }

         
        $status = self::OVERPAIR;
        // done
        return new self($status);

        
    }

    public static function cancelled(): self
    {
        return new self(self::CANCELLED);
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

    public function isUnbilled(): bool
    {
        return $this->value === self::UNBILLED;
    }

    public function isBilled(): bool
    {
        return $this->value === self::BILLED;
    }

    public function isPartiallyPaid(): bool
    {
        return $this->value === self::PARTIALLY_PAID;
    }
    public function isPaid(): bool
    {
        return $this->value === self::PAID;
    }
    public function isRefunded(): bool
    {
        return $this->value === self::REFUNDED;
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

    public function markBilled(): self
    {
        $this->guard([self::COMPLETED], self::BILLED);
        return self::billed();
    }

    public function markPartiallyPaid(): self
    {
        $this->guard([self::BILLED, self::PARTIALLY_PAID], self::PARTIALLY_PAID);
        return self::partiallyPaid();
    }

    public function markPaid(): self
    {
        $this->guard([self::BILLED, self::PARTIALLY_PAID], self::PAID);
        return self::paid();
    }

    public function markRefunded(): self
    {
        $this->guard([self::PAID], self::REFUNDED);
        return self::refunded();
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
