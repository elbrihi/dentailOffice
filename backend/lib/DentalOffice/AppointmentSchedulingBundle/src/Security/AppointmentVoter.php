<?php

declare(strict_types=1);

namespace App\AppointmentBundle\Infrastructure\Security;

use DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Entity\AppointmentOrmEntity as EntityAppointmentOrmEntity;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use  DentalOffice\UserBundle\Domain\Entity\User;


final class AppointmentVoter extends Voter
{
    
    public const VIEW = 'APPOINTMENT_VIEW';
    public const CREATE = 'APPOINTMENT_CREATE';
    public const UPDATE = 'APPOINTMENT_UPDATE';
    public const CONFIRM = 'APPOINTMENT_CONFIRM';
    public const CANCEL = 'APPOINTMENT_CANCEL';
    public const COMPLETE = 'APPOINTMENT_COMPLETE';
    public const RESCHEDULE = 'APPOINTMENT_RESCHEDULE';

    protected function supports(string $attribute, mixed $subject): bool
    {



        return in_array($attribute, [
            self::VIEW,
            self::CREATE,
            self::UPDATE,
            self::CONFIRM,
            self::CANCEL,
            self::COMPLETE,
            self::RESCHEDULE,
        ], true);
    }

    protected function voteOnAttribute(
        string $attribute,
        mixed $appointment,
        TokenInterface $token
    ): bool {


        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        // OWNER = GOD MODE
        if ($this->hasRole($user, 'ROLE_OWNER')) {
            return true;
        }

        return match ($attribute) {

            self::VIEW => $this->canView($user),

            self::CREATE => $this->canCreate($user),

            self::UPDATE => $this->canUpdate($user, $appointment),

            self::CONFIRM => $this->canConfirm($user, $appointment),

            self::CANCEL => $this->canCancel($user, $appointment),

            self::COMPLETE => $this->canComplete($user, $appointment),

            self::RESCHEDULE => $this->canReschedule($user, $appointment),

            default => false,
        };
    }

    // ===============================
    // RULES
    // ===============================

    private function canView(User $user): bool
    {
        return $this->hasAnyRole($user, [
            'ROLE_MANAGER',
            'ROLE_DENTIST',
            'ROLE_RECEPTIONIST',
            'ROLE_ASSISTANT',
            
        ]);
    }

    private function canCreate(User $user): bool
    {
        return $this->hasAnyRole($user, [
            'ROLE_MANAGER',
            'ROLE_RECEPTIONIST',
        ]);
    }

    private function canUpdate(User $user, ?EntityAppointmentOrmEntity $appointment): bool
    {
        if (!$this->hasAnyRole($user, ['ROLE_MANAGER', 'ROLE_RECEPTIONIST'])) {
            return false;
        }

        // ❗ Cannot update completed appointment
        return !$this->isCompleted($appointment);
    }

    private function canConfirm(User $user, ?EntityAppointmentOrmEntity $appointment): bool
    {
        if (!$this->hasAnyRole($user, ['ROLE_MANAGER', 'ROLE_RECEPTIONIST'])) {
            return false;
        }

        return $this->isScheduled($appointment);
    }

    private function canCancel(User $user, ?EntityAppointmentOrmEntity $appointment): bool
    {
        if (!$this->hasAnyRole($user, ['ROLE_MANAGER', 'ROLE_RECEPTIONIST'])) {
            return false;
        }

        return !$this->isCompleted($appointment);
    }

    private function canComplete(User $user, ?EntityAppointmentOrmEntity $appointment): bool
    {
        if (!$this->hasRole($user, 'ROLE_DENTIST')) {
            return false;
        }

        // 🔥 Critical rule
        return $this->isConfirmed($appointment);
    }

    private function canReschedule(User $user, ?EntityAppointmentOrmEntity $appointment): bool
    {
        if (!$this->hasAnyRole($user, ['ROLE_MANAGER', 'ROLE_RECEPTIONIST'])) {
            return false;
        }

        return !$this->isCompleted($appointment);
    }

    // ===============================
    // HELPERS
    // ===============================

    private function hasRole(User $user, string $role): bool
    {
        return in_array($role, $user->getRoles(), true);
    }

    private function hasAnyRole(User $user, array $roles): bool
    {
        return count(array_intersect($roles, $user->getRoles())) > 0;
    }

    private function isScheduled(?EntityAppointmentOrmEntity $appointment): bool
    {
        return $appointment?->getStatus() === 'scheduled';
    }

    private function isConfirmed(?EntityAppointmentOrmEntity $appointment): bool
    {
        return $appointment?->getStatus() === 'confirmed';
    }

    private function isCompleted(?EntityAppointmentOrmEntity $appointment): bool
    {
        return $appointment?->getStatus() === 'completed';
    }
}