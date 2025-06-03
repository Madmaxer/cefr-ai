<?php

namespace App\Services\AiProviders;

use App\Contracts\AiProvider;
use App\Enums\Language;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\App;

abstract class AbstractAiProvider implements AiProvider
{
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

    public function __construct(
        protected readonly Client $httpClient,
        protected readonly string $providerName
    ) {}

    public function sendRequest(array $payload): array
    {
        $language = $payload['language'] ?? 'english';
        $locale = $this->localeMap[$language] ?? 'en';
        App::setLocale($locale);

        $apiResponse = $this->getAiResponse($payload);

        if (isset($apiResponse['finished']) && $apiResponse['finished']) {
            return [
                'finished' => true,
                'level' => $apiResponse['level'],
                'description' => $apiResponse['text'], // Cała odpowiedź Claude jako opis
            ];
        }

        return ['next_question' => $apiResponse['text']];
    }

    abstract protected function getAiResponse(array $payload): array;
    abstract public function getEndpoint(): string;
    abstract protected function makeHttpRequest(array $body): array;

    protected function mockAiResponse(array $payload): array
    {
        $language = $payload['language'] ?? 'english';
        $locale = $this->localeMap[$language] ?? 'en';
        App::setLocale($locale);

        return ['text' => 'Mock response for ' . ($payload['message'] ?? 'test')];
    }
}
