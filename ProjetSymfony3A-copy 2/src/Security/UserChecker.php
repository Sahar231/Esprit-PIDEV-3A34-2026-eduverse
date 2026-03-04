<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if ($user->isRejected()) {
            throw new CustomUserMessageAuthenticationException('Votre compte a été rejeté par l\'administrateur.');
        }

        if ($user->isBlocked()) {
            throw new CustomUserMessageAuthenticationException('Votre compte est bloqué. Veuillez contacter l\'administrateur.');
        }

        if (!$user->isApproved()) {
            throw new CustomUserMessageAuthenticationException('Votre compte est en attente d\'approbation par l\'administrateur.');
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
    // No checks needed after authentication
    }
}
