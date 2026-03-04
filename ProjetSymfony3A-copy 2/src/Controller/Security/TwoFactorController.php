<?php

namespace App\Controller\Security;

use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorFormRendererInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TwoFactorController extends AbstractController
{
    /**
     * @Route("/2fa", name="2fa_login")
     */
    public function form(TwoFactorFormRendererInterface $formRenderer): Response
    {
        return $formRenderer->renderForm($this->container->get('request_stack')->getCurrentRequest(), [
            'authenticationError' => null // Placeholder
        ]);
    }

    /**
     * @Route("/2fa_check", name="2fa_login_check")
     */
    public function check(): void
    {
        throw new \RuntimeException('You must configure the check path to be handled by the firewall.');
    }
}
