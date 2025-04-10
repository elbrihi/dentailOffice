<?php

namespace DentalOffice\UserBundle\Application\Service\Manager;

use DentalOffice\UserBundle\Domain\Entity\User;


interface AuthTokenManagerInterface
{
    public function postAuthTokens(array $request ): User;
}