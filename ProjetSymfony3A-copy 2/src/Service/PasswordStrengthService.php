<?php

namespace App\Service;

class PasswordStrengthService
{
    /**
     * Génère un mot de passe fort, aléatoire et complexe.
     * Contient au moins une minuscule, une majuscule, un chiffre, et un caractère spécial.
     *
     * @param int $length La longueur du mot de passe (défaut: 12).
     * @return string
     */
    public function generateSecurePassword(int $length = 12): string
    {
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numbers = '0123456789';
        $specials = '!@#$%^&*()-_=+[]{};:,.<>?';

        $allCharacters = $lowercase . $uppercase . $numbers . $specials;
        
        $password = array();
        
        // Ensure at least one of each required type
        $password[] = $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password[] = $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password[] = $numbers[random_int(0, strlen($numbers) - 1)];
        $password[] = $specials[random_int(0, strlen($specials) - 1)];

        // Fill the rest randomly
        for ($i = 4; $i < $length; $i++) {
            $password[] = $allCharacters[random_int(0, strlen($allCharacters) - 1)];
        }

        // Shuffle the result so the predictable pattern is mixed
        shuffle($password);

        return implode('', $password);
    }
}
