<?php

namespace App\Controller;

use App\Service\TranslationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TranslateController extends AbstractController
{
    private TranslationService $translationService;

    public function __construct(TranslationService $translationService)
    {
        $this->translationService = $translationService;
    }

    /**
     * @Route("/api/translate", name="api_translate", methods={"POST"})
     */
    public function translate(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return new JsonResponse(['error' => 'Invalid JSON body'], 400);
        }

        $text = $data['text'] ?? null;
        $target = $data['target'] ?? null;
        $source = $data['source'] ?? null;

        if (!$text || !$target) {
            return new JsonResponse(['error' => 'Both "text" and "target" fields are required'], 400);
        }

        try {
            $translated = $this->translationService->translate($text, $target, $source);
        } catch (\RuntimeException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }

        return new JsonResponse(['translated' => $translated]);
    }

    /**
     * small demo page to manually test translation via UI
     *
     * @Route("/translate-demo", name="translate_demo", methods={"GET"})
     */
    public function demo(): \Symfony\Component\HttpFoundation\Response
    {
        return $this->render('translate_example.html.twig');
    }
}
