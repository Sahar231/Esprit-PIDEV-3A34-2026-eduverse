<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use RuntimeException;

class TranslationService
{
    private HttpClientInterface $client;
    private string $provider;
    private string $deeplUrl;
    private string $deeplKey;
    private string $libreUrl;
    private string $libreKey;

    public function __construct(
        HttpClientInterface $client,
        string $provider,
        string $deeplUrl,
        string $deeplKey,
        string $libreUrl,
        string $libreKey
    ) {
        $this->client = $client;
        $this->provider = $provider;
        $this->deeplUrl = rtrim($deeplUrl, '/');
        $this->deeplKey = $deeplKey;
        $this->libreUrl = rtrim($libreUrl, '/');
        $this->libreKey = $libreKey;
    }

    /**
     * Translate a text string to a target language.
     *
     * @param string $text
     * @param string $targetLang
     * @param string|null $sourceLang
     * @return string
     * @throws RuntimeException when provider returns error or unsupported provider
     */
    public function translate(string $text, string $targetLang, ?string $sourceLang = null): string
    {
        if ($this->provider === 'deepl') {
            return $this->translateWithDeepl($text, $targetLang, $sourceLang);
        }

        if ($this->provider === 'libretranslate') {
            return $this->translateWithLibre($text, $targetLang, $sourceLang);
        }

        throw new RuntimeException(sprintf('Unsupported translation provider "%s"', $this->provider));
    }

    private function translateWithDeepl(string $text, string $targetLang, ?string $sourceLang = null): string
    {
        $url = $this->deeplUrl . '/v2/translate';
        $params = [
            'auth_key' => $this->deeplKey,
            'text' => $text,
            'target_lang' => strtoupper($targetLang),
        ];
        if ($sourceLang) {
            $params['source_lang'] = strtoupper($sourceLang);
        }

        try {
            $response = $this->client->request('POST', $url, [
                'body' => $params,
                'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            ]);
        } catch (TransportExceptionInterface $e) {
            throw new RuntimeException('Network error while calling DeepL: ' . $e->getMessage());
        }

        if (200 !== $response->getStatusCode()) {
            throw new RuntimeException('DeepL returned HTTP ' . $response->getStatusCode());
        }

        $data = $response->toArray(false);
        if (!isset($data['translations'][0]['text'])) {
            throw new RuntimeException('Unexpected DeepL response: ' . json_encode($data));
        }

        return $data['translations'][0]['text'];
    }

    private function translateWithLibre(string $text, string $targetLang, ?string $sourceLang = null): string
    {
        $url = $this->libreUrl . '/translate';
        $params = [
            'q' => $text,
            'target' => $targetLang,
            'format' => 'text',
        ];
        if ($sourceLang) {
            $params['source'] = $sourceLang;
        }
        if (!empty($this->libreKey) && 'CHANGE_ME' !== $this->libreKey) {
            $params['api_key'] = $this->libreKey;
        }

        try {
            $response = $this->client->request('POST', $url, [
                'body' => $params,
            ]);
        } catch (TransportExceptionInterface $e) {
            throw new RuntimeException('Network error while calling LibreTranslate: ' . $e->getMessage());
        }

        if (200 !== $response->getStatusCode()) {
            throw new RuntimeException('LibreTranslate returned HTTP ' . $response->getStatusCode());
        }

        $data = $response->toArray(false);
        if (!isset($data['translatedText'])) {
            throw new RuntimeException('Unexpected LibreTranslate response: ' . json_encode($data));
        }

        return $data['translatedText'];
    }
}
