<?php
namespace DentalOffice\AppointmentSchedulingBundle\tests\Functional\Domain\ValueObject;


use DentalOffice\AppointmentSchedulingBundle\Domain\Exception\InvalidTimeSlot;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\TimeSlot;
use PHPUnit\Framework\TestCase;

class TimeSlotTest  extends TestCase
{
    private \DateTimeImmutable $start;
    private \DateTimeImmutable $end;

    protected function setUp(): void
    {
       
        parent::setUp();

        $this->start = new \DateTimeImmutable('2026-03-01 09:00:00');
        $this->end   = new \DateTimeImmutable('2026-03-01 10:00:00');
    }

    public function test_it_creates_valid_timeslot(): void
    {
        $timeSlot = new TimeSlot($this->start,$this->end);
        $this->assertInstanceOf(TimeSlot::class, $timeSlot);
    }

    public function test_it_throws_exception_if_end_is_before_start(): void
    {
        $this->expectException(InvalidTimeSlot::class);

        new TimeSlot(
            new \DateTimeImmutable('2026-03-01 10:00:00'),
            new \DateTimeImmutable('2026-03-01 09:00:00')
        );
    }
}