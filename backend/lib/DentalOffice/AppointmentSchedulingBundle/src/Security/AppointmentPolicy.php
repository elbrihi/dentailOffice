<?php

namespace DentalOffice\AppointmentSchedulingBundle\Application\Security;

use DentalOffice\UserBundle\Domain\Entity\User;
use DentalOffice\AppointmentSchedulingBundle\Domain\Aggregate\Appointment;

final class AppointmentPolicy
{
    public function canConfirm(User $user, Appointment $appointment): bool
    {
        return $this->hasRole($user, ['ROLE_RECEPTIONIST', 'ROLE_MANAGER']);
    }

    public function canCancel(User $user, Appointment $appointment): bool
    {
        return $this->hasRole($user, ['ROLE_MANAGER', 'ROLE_RECEPTIONIST']);
    }

    public function canComplete(User $user, Appointment $appointment): bool
    {
        return $this->hasRole($user, ['ROLE_DENTIST']);
    }

    public function canReschedule(User $user, Appointment $appointment): bool
    {
        return $this->hasRole($user, ['ROLE_MANAGER', 'ROLE_RECEPTIONIST']);
    }

    public function canView(User $user): bool
    {
        return $this->hasRole($user, [
            'ROLE_MANAGER',
            'ROLE_DENTIST',
            'ROLE_RECEPTIONIST',
            'ROLE_ASSISTANT'
        ]);
    }

    private function hasRole(User $user, array $roles): bool
    {
        return !empty(array_intersect($roles, $user->getRoles()));
    }
}