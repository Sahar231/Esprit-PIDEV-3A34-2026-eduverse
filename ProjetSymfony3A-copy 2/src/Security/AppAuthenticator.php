<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Bundle\SecurityBundle\Security;

class AppAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private Security $security
    ) {
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->request->get('email', '');
        $password = $request->request->get('password', '');
        $csrfToken = $request->request->get('_csrf_token', '');

        // Strict PHP-only validation: Check for empty fields since HTML5 validation will be removed
        if (empty($email)) {
            throw new \Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException('Veuillez saisir votre adresse email.');
        }
        if (empty($password)) {
            throw new \Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException('Veuillez saisir votre mot de passe.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException('Le format de l\'adresse email n\'est pas valide.');
        }

        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $email);

        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($password),
        [
            new CsrfTokenBadge('authenticate', $csrfToken),
            new RememberMeBadge(),
        ]
            );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        /** @var \App\Entity\User $user */
        $user = $token->getUser();

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return new RedirectResponse($this->urlGenerator->generate('admin_dashboard'));
        }

        if ($this->security->isGranted('ROLE_INSTRUCTOR')) {
            return new RedirectResponse($this->urlGenerator->generate('instructor_dashboard'));
        }

        if ($this->security->isGranted('ROLE_STUDENT')) {
            return new RedirectResponse($this->urlGenerator->generate('student_dashboard'));
        }

        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->urlGenerator->generate('app_home'));
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
