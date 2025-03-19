<?php

namespace App\Services\AiProviders;

use App\Contracts\AiProvider;
use App\Enums\Language;
use App\Models\LanguageTest;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

abstract class AbstractAiProvider implements AiProvider
{
    protected Client $httpClient;
    protected string $providerName;

    protected array $questionKeys = [
        'questions.tell_me_about_yourself',
        'questions.describe_favorite_book',
        'questions.free_time',
        'questions.place_visited',
        'questions.future_plans',
    ];

    protected array $localeMap = [
        Language::ENGLISH->value => 'en',
        Language::SPANISH->value => 'es',
        Language::FRENCH->value => 'fr',
        Language::GERMAN->value => 'de',
        Language::ITALIAN->value => 'it',
        Language::PORTUGUESE->value => 'pt',
        Language::RUSSIAN->value => 'ru',
        Language::POLISH->value => 'pl',
        Language::DUTCH->value => 'nl',
        Language::SWEDISH->value => 'sv',
    ];

    public function __construct()
    {
        $this->httpClient = new Client([
            'timeout' => 10.0,
        ]);
        $this->providerName = config('ai.provider', 'xai');
    }

    public function sendRequest(array $payload): array
    {
        $response = $this->getAiResponse($payload);

        if ($response['finished']) {
            LanguageTest::create([
                'user_id' => $payload['user_id'],
                'language' => $payload['language'],
                'level' => $response['level'],
                'description' => $response['description'],
                'tested_at' => now(),
            ]);
        }

        return $response;
    }

    abstract protected function getAiResponse(array $payload): array;
    abstract public function getEndpoint(): string;

    protected function makeHttpRequest(array $body): array
    {
        $config = config("ai.providers.{$this->providerName}");
        $endpoint = $this->getEndpoint();
        $apiKey = $config['api_key'] ?? '';

        try {
            $response = $this->httpClient->post($endpoint, [
                'headers' => [
                    'Authorization' => "Bearer {$apiKey}",
                    'Content-Type' => 'application/json',
                ],
                'json' => $body,
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            Log::debug('error', ['exception' => $e->getMessage()]);

            return $this->mockAiResponse($body);
        }
    }

    protected function mockAiResponse(array $payload): array
    {
        static $step = 0;
        static $totalWordCount = 0;

        $language = $payload['language'];
        $locale = $this->localeMap[$language] ?? 'en'; // Domyślnie 'en'
        App::setLocale($locale);

        $response = $payload['message'] ?? '';
        $wordCount = str_word_count($response);
        $totalWordCount += $wordCount;

        $step++;

        if ($step < count($this->questionKeys)) {
            return ['next_question' => __($this->questionKeys[$step])];
        }

        $averageWordCount = $totalWordCount / $step;

        $level = match (true) {
            $averageWordCount < 5 => 'A1',
            $averageWordCount < 10 => 'A2',
            $averageWordCount < 20 => 'B1',
            $averageWordCount < 30 => 'B2',
            $averageWordCount < 40 => 'C1',
            default => 'C2',
        };

        $description = __("questions.levels.{$level}");

        $step = 0;
        $totalWordCount = 0;

        return [
            'finished' => true,
            'level' => $level,
            'description' => $description,
        ];
    }
}
