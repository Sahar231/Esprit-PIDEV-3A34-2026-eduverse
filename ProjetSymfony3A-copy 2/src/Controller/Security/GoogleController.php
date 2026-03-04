<?php

namespace App\Controller\Security;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;

class GoogleController extends AbstractController
{
    #[Route('/connect/google', name: 'connect_google')]
    public function connectAction(ClientRegistry $clientRegistry, \Symfony\Component\HttpFoundation\Request $request): RedirectResponse
    {
        // Store the intended role in session and cookie if provided (e.g. from signup buttons)
        $role = $request->query->get('role');
        if ($role) {
            $session = $request->getSession();
            $session->set('oauth_role', $role);
            $session->save(); // Force save session before redirect

            // Get the redirect response from the client
            $response = $clientRegistry->getClient('google')->redirect([], []);
            
            // Add a cookie as a fallback with better persistence
            $response->headers->setCookie(new \Symfony\Component\HttpFoundation\Cookie(
                'oauth_role', 
                $role, 
                time() + 1800, // 30 minutes
                '/', 
                null, 
                false, 
                true, 
                false, 
                'lax'
            ));
            return $response;
        }

        // NO ROLE provided (Login flow)
        $session = $request->getSession();
        $session->remove('oauth_role');
        
        $response = $clientRegistry->getClient('google')->redirect([], []);
        $response->headers->clearCookie('oauth_role'); // Ensure cookie is also cleared
        return $response;
    }

    #[Route('/connect/google/check', name: 'connect_google_check')]
    public function connectCheckAction(): void
    {
        // ** if you want to *authenticate* the user, then
        // leave this method blank and create a Guard authenticator
    }
}
