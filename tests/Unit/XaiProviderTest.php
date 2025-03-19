<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\AiProviders\XaiProvider;
use ReflectionClass;
use Illuminate\Translation\Translator;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Translation\FileLoader;

class XaiProviderTest extends TestCase
{
    protected XaiProvider $provider;
    protected Translator $translator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->provider = new XaiProvider();

        // Inicjalizacja tłumacza dla testów
        $loader = new FileLoader(new Filesystem(), __DIR__ . '/../../lang');
        $this->translator = new Translator($loader, 'en');
        app()->instance('translator', $this->translator);

        // Reset statycznych zmiennych
        $reflection = new ReflectionClass(AbstractAiProvider::class);
        $step = $reflection->getProperty('step');
        $totalWordCount = $reflection->getProperty('totalWordCount');
        $step->setAccessible(true);
        $totalWordCount->setAccessible(true);
        $step->setValue(0);
        $totalWordCount->setValue(0);
    }

    public function testA1LevelForVeryShortResponses()
    {
        $this->translator->setLocale('english');
        $payload = ['language' => 'english', 'message' => 'Yes.'];

        for ($i = 0; $i < 4; $i++) {
            $response = $this->provider->sendRequest($payload);
            $this->assertArrayHasKey('next_question', $response);
        }

        $finalResponse = $this->provider->sendRequest($payload);
        $this->assertTrue($finalResponse['finished']);
        $this->assertEquals('A1', $finalResponse['level']);
    }

    public function testB2LevelForLongerResponses()
    {
        $this->translator->setLocale('english');
        $payload = ['language' => 'english', 'message' => 'I am a student who enjoys reading books and playing games with friends.'];

        for ($i = 0; $i < 4; $i++) {
            $response = $this->provider->sendRequest($payload);
            $this->assertArrayHasKey('next_question', $response);
        }

        $finalResponse = $this->provider->sendRequest($payload);
        $this->assertTrue($finalResponse['finished']);
        $this->assertEquals('B2', $finalResponse['level']);
    }

    public function testPolishLanguageResponses()
    {
        $this->translator->setLocale('polish');
        $payload = ['language' => 'polish', 'message' => 'Jestem studentem, który lubi czytać książki i grać w gry.'];

        for ($i = 0; $i < 4; $i++) {
            $response = $this->provider->sendRequest($payload);
            $this->assertArrayHasKey('next_question', $response);
            $this->assertEquals(trans($this->getQuestionKey($i + 1)), $response['next_question']);
        }

        $finalResponse = $this->provider->sendRequest($payload);
        $this->assertTrue($finalResponse['finished']);
        $this->assertEquals('B2', $finalResponse['level']);
    }

    private function getQuestionKey(int $step): string
    {
        $keys = [
            'questions.tell_me_about_yourself',
            'questions.describe_favorite_book',
            'questions.free_time',
            'questions.place_visited',
            'questions.future_plans',
        ];
        return $keys[$step];
    }
}
