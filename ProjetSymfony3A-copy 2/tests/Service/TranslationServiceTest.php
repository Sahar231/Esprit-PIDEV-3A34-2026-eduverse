<?php

namespace App\Tests\Service;

use App\Service\TranslationService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class TranslationServiceTest extends TestCase
{
    public function testLibreTranslate(): void
    {
        $responseData = ['translatedText' => 'bonjour'];
        $mock = new MockResponse(json_encode($responseData), ['http_code' => 200]);
        $client = new MockHttpClient($mock);

        $service = new TranslationService(
            $client,
            'libretranslate',
            'http://deepl',
            'KEY',
            'http://libre',
            'KEY'
        );

        $result = $service->translate('hello', 'fr');
        $this->assertSame('bonjour', $result);
    }

    public function testDeepL(): void
    {
        $responseData = ['translations' => [['text' => 'au revoir']]];
        $mock = new MockResponse(json_encode($responseData), ['http_code' => 200]);
        $client = new MockHttpClient($mock);

        $service = new TranslationService(
            $client,
            'deepl',
            'http://deepl',
            'KEY',
            'http://libre',
            'KEY'
        );

        $result = $service->translate('goodbye', 'fr');
        $this->assertSame('au revoir', $result);
    }

    public function testUnsupportedProviderThrows(): void
    {
        $client = new MockHttpClient();
        $service = new TranslationService(
            $client,
            'unknown',
            '',
            '',
            '',
            ''
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unsupported translation provider');
        $service->translate('foo', 'bar');
    }
}
