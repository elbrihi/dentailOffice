<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use DentalOffice\UserBundle\Domain\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * ==========================================================
 * 🏥 SmileCare Dental – Initial Users
 * ==========================================================
 */

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $this->createUser(
            $manager,
            'ali.owner',
            'owner123',
            ['ROLE_OWNER']
        );

        $this->createUser(
            $manager,
            'ali.manager',
            'manager123',
            ['ROLE_MANAGER']
        );

        $this->createUser(
            $manager,
            'dr.karim',
            'dentist123',
            ['ROLE_DENTIST']
        );

        $this->createUser(
            $manager,
            'fatima',
            'reception123',
            ['ROLE_RECEPTIONIST']
        );

        $this->createUser(
            $manager,
            'sara',
            'assistant123',
            ['ROLE_ASSISTANT']
        );

        $manager->flush();
    }

    /**
     * ==========================================================
     * 🔧 Helper Method
     * Create a new user
     * ==========================================================
     */

    private function createUser(
        ObjectManager $manager,
        string $username,
        string $password,
        array $roles
    ): void {
        $user = new User();

        $user->setUsername($username);
        $user->setRoles($roles);

        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            $password
        );

        $user->setPassword($hashedPassword);

        $user->setApiToken(
            base64_encode(random_bytes(50))
        );

        $manager->persist($user);
    }
}