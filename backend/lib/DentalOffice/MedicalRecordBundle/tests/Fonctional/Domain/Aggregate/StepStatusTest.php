<?php

namespace DentalOffice\MedicalRecordBundle\Tests\Fonctional\Domain\Aggregate;

use DentalOffice\MedicalRecordBundle\Domain\ValueObject\StepStatus;
use PHPUnit\Framework\TestCase;

class StepStatusTest extends TestCase
{
    public function test_step_status_is_planned()
    {
        $stepStatus = StepStatus::planned();

        $this->assertEquals('planned', $stepStatus->getStatus());
    }

    public function test_step_status_is_scheduled()
    {
        $stepStatus = StepStatus::scheduled('planned');

        $this->assertEquals('scheduled', $stepStatus->getStatus());
    }

    public function test_step_status_is_in_progress()
    {
        $stepStatus = StepStatus::inProgress('scheduled');

        $this->assertEquals('in_progress', $stepStatus->getStatus());
    }

    public function test_step_status_is_completed()
    {
        $stepStatus = StepStatus::completed('in_progress');

        $this->assertEquals('completed', $stepStatus->getStatus());
    }

    public function test_step_status_is_cancelled()
    {
        $stepStatus = StepStatus::cancelled('in_progress');

        $this->assertEquals('cancelled', $stepStatus->getStatus());
    }
}