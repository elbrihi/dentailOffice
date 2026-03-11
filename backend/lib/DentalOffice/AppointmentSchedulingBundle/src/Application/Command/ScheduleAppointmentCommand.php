<?php

namespace DentalOffice\AppointmentSchedulingBundle\Application\Command;


final class ScheduleAppointmentCommand
{
  public function __construct(
        public string $patientId,
        public \DateTimeImmutable $start,
        public \DateTimeImmutable $end
  ) {}
}
