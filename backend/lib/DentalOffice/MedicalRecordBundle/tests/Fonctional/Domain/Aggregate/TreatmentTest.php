<?php

namespace DentalOffice\MedicalRecordBundle\Tests\Fonctional\Domain\Aggregate;

use DentalOffice\MedicalRecordBundle\Domain\Aggregate\Step;
use DentalOffice\MedicalRecordBundle\Domain\Aggregate\Treatment;
use DentalOffice\MedicalRecordBundle\Domain\ValueObject\StepStatus;
use PHPUnit\Framework\TestCase;

class TreatmentTest extends TestCase
{
       /* =========================
     * 🎯 RULE: all planned → planned
     * ========================= */
    public function test_all_steps_planned_returns_planned(): void
    {
        $treatment = Treatment::createTreatmentPlan(1, 'Test', 'ROOT');

        $treatment->addStep($this->step(1, StepStatus::planned()));
        $treatment->addStep($this->step(2, StepStatus::planned()));

        $this->assertSame('planned', $treatment->getStatus()->getStatus());
    }


    /* =========================
     * 🎯 RULE: any in_progress → in_progress
     * ========================= */
    public function test_any_in_progress_returns_in_progress(): void
    {
        $treatment = Treatment::createTreatmentPlan(1, 'Test', 'ROOT');

        $treatment->addStep($this->step(1, StepStatus::planned()));
        $treatment->addStep($this->step(2, StepStatus::inProgress('scheduled')));

        $this->assertSame('in_progress', $treatment->getStatus()->getStatus());
    }


    /* =========================
     * 🎯 RULE: all completed → completed
     * ========================= */
    public function test_all_completed_returns_completed(): void
    {
        $treatment = Treatment::createTreatmentPlan(1, 'Test', 'ROOT');

        $treatment->addStep($this->step(1, StepStatus::completed('in_progress')));
        $treatment->addStep($this->step(2, StepStatus::completed('in_progress')));

        $this->assertSame('completed', $treatment->getStatus()->getStatus());
    }


    /* =========================
     * 🎯 RULE: all cancelled → cancelled
     * ========================= */
    public function test_all_cancelled_returns_cancelled(): void
    {
        $treatment = Treatment::createTreatmentPlan(1, 'Test', 'ROOT');

        $treatment->addStep($this->step(1, StepStatus::cancelled('in_progress')));
        $treatment->addStep($this->step(2, StepStatus::cancelled('in_progress')));

        $this->assertSame('cancelled', $treatment->getStatus()->getStatus());
    }


    /* =========================
     * 🎯 RULE: any scheduled → scheduled
     * ========================= */
    public function test_any_scheduled_returns_scheduled(): void
    {
        $treatment = Treatment::createTreatmentPlan(1, 'Test', 'ROOT');

        $treatment->addStep($this->step(1, StepStatus::planned()));
        $treatment->addStep($this->step(2, StepStatus::scheduled('planned')));

        $this->assertSame('scheduled', $treatment->getStatus()->getStatus());
    }


    /* =========================
     * 🎯 RULE: fallback → in_progress
     * ========================= */
    public function test_mixed_states_fallback_to_in_progress(): void
    {
        $treatment = Treatment::createTreatmentPlan(1, 'Test', 'ROOT');

        $treatment->addStep($this->step(1, StepStatus::completed('in_progress')));
        $treatment->addStep($this->step(2, StepStatus::planned()));

        $this->assertSame('in_progress', $treatment->getStatus()->getStatus());
    }


    /* =========================
     * 🛠️ HELPER (clean builder)
     * ========================= */
    private function step(int $id, StepStatus $status): Step
    {
        return Step::createStep(
            $id,
            'Step ' . $id,
            1,
            $status,
            new \DateTime()
        );
    }
}