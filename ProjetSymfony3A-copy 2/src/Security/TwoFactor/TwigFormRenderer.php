<?php

namespace App\Security\TwoFactor;

use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorFormRendererInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class TwigFormRenderer implements TwoFactorFormRendererInterface
{
    private Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function renderForm(Request $request, array $templateVars): Response
    {
        return new Response($this->twig->render('security/2fa_form.html.twig', $templateVars));
    }
}
