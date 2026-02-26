<?php

namespace App\Security\TwoFactor;

use App\Entity\User;
use App\Service\TwilioService;
use Doctrine\ORM\EntityManagerInterface;
use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContextInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorFormRendererInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderInterface;

class TwilioSmsProvider implements TwoFactorProviderInterface
{
    private $twilioService;
    private $entityManager;
    private $formRenderer;

    public function __construct(TwilioService $twilioService, EntityManagerInterface $entityManager, TwigFormRenderer $formRenderer)
    {
        $this->twilioService = $twilioService;
        $this->entityManager = $entityManager;
        $this->formRenderer = $formRenderer;
    }

    public function beginAuthentication(AuthenticationContextInterface $context): bool
    {
        /** @var User $user */
        $user = $context->getUser();

        return $user->isTwoFactorEnabled() && null !== $user->getPhoneNumber();
    }

    public function prepareAuthentication(object $user): void
    {
        if (!$user instanceof User || !$user->getPhoneNumber()) {
            return;
        }

        // Generate a 6-digit code
        $code = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        $user->setTwoFactorCode($code);
        
        // Expiration in 5 minutes
        $expiration = (new \DateTimeImmutable())->modify('+5 minutes');
        $user->setTwoFactorExpiresAt($expiration);

        $this->entityManager->flush();

        // Send SMS
        $message = sprintf('Eduverse - Votre code d\'authentification (valable 5 min) est : %s', $code);
        $this->twilioService->sendSms($user->getPhoneNumber(), $message);
    }

    public function validateAuthenticationCode(object $user, string $authenticationCode): bool
    {
        if (!$user instanceof User) {
            return false;
        }

        // Check expiration
        if (null !== $user->getTwoFactorExpiresAt() && new \DateTimeImmutable() > $user->getTwoFactorExpiresAt()) {
            return false;
        }

        if ($user->getTwoFactorCode() === $authenticationCode) {
            // Reset code after successful validation
            $user->setTwoFactorCode(null);
            $user->setTwoFactorExpiresAt(null);
            $this->entityManager->flush();
            return true;
        }

        return false;
    }

    public function getFormRenderer(): TwoFactorFormRendererInterface
    {
        return $this->formRenderer;
    }
}
