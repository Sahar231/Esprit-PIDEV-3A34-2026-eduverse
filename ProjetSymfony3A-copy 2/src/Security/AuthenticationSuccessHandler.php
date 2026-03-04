<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class AuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    public function __construct(private RouterInterface $router)
    {
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): Response
    {
        $user = $token->getUser();

        // Redirect to admin dashboard if user is admin
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return new RedirectResponse($this->router->generate('admin_dashboard'));
        }

        // Redirect to instructor dashboard if instructor
        if (in_array('ROLE_INSTRUCTOR', $user->getRoles())) {
            return new RedirectResponse($this->router->generate('instructor_dashboard'));
        }

        // Default redirect for students
        return new RedirectResponse($this->router->generate('student_dashboard'));
    }
}
