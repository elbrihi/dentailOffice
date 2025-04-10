<?php

namespace DentalOffice\UserBundle\Infrastructure\Persistence\Doctrine\Repository;

use DentalOffice\UserBundle\Domain\Entity\AuthToken;

interface AuthTokenRepositoryInterface
{
    public function findAuthTokenByValue(string $value): ?AuthToken;
}
