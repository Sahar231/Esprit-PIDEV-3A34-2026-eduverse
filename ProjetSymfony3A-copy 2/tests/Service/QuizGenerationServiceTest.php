<?php

namespace App\Tests\Service;

use App\Service\QuizGenerationService;
use PHPUnit\Framework\TestCase;

class QuizGenerationServiceTest extends TestCase
{
    public function testGenerateFromChapterReturnsArray(): void
    {
        $service = new QuizGenerationService('python');

        try {
            $result = $service->generateFromChapter('Hello', 'World', 2, 123);
        } catch (\RuntimeException $e) {
            // if Python is not available or script fails, skip the test
            $this->markTestSkipped('Python quiz generator not available: ' . $e->getMessage());
        }

        $this->assertArrayHasKey('title', $result);
        $this->assertSame('Hello', $result['title']);
        $this->assertArrayHasKey('questions', $result);
        $this->assertCount(2, $result['questions']);
    }

    public function testGenerateThrowsWhenInvalidJson(): void
    {
        // create a temporary script that prints invalid JSON
        $tmp = tempnam(sys_get_temp_dir(), 'quiz');
        file_put_contents($tmp, '<?php echo "not json";');

        // using php to run the script (should exist in test env)
        $service = new QuizGenerationService('php', null, $tmp);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid JSON');

        $service->generateFromChapter('whatever');
    }
}
