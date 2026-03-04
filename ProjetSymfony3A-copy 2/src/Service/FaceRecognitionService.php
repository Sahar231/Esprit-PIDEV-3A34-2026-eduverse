<?php

namespace App\Service;

class FaceRecognitionService
{
    /**
     * Calcule la distance euclidienne entre deux descripteurs faciaux (tableaux de 128 flottants).
     * Plus la distance est faible, plus les visages se ressemblent.
     * Le seuil standard avec face-api.js (modèle ssdMobilenetv1/faceRecognitionNet) est généralement de 0.6.
     *
     * @param float[] $descriptor1 Array of floats (128‑dim descriptor)
     * @param float[] $descriptor2 Array of floats (128‑dim descriptor)
     * @return float
     */
    public function euclideanDistance(array $descriptor1, array $descriptor2): float
    {
        if (count($descriptor1) !== count($descriptor2)) {
            throw new \InvalidArgumentException("Les descripteurs doivent avoir la même longueur.");
        }

        $sum = 0.0;
        foreach ($descriptor1 as $index => $value1) {
            $value2 = $descriptor2[$index];
            $diff = $value1 - $value2;
            $sum += $diff * $diff;
        }

        return sqrt($sum);
    }

    /**
     * Vérifie si deux descripteurs appartiennent à la même personne en fonction d'un seuil.
     *
     * @param array $storedDescriptor Le descripteur stocké en base de données.
     * @param array $liveDescriptor Le descripteur capturé lors de la connexion.
     * @param float $threshold Le seuil maximum accepté (défaut: 0.6).
     * @return bool
     */
    public function areFacesMatching(array $storedDescriptor, array $liveDescriptor, float $threshold = 0.6): bool
    {
        $distance = $this->euclideanDistance($storedDescriptor, $liveDescriptor);
        return $distance < $threshold;
    }
}
