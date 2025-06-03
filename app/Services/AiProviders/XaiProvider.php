<?php

namespace App\Services\AiProviders;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class XaiProvider extends AbstractAiProvider
{
    public function __construct(Client $httpClient)
    {
        parent::__construct($httpClient, 'xai');
    }

    public function getEndpoint(): string
    {
        return config('ai.providers.xai.base_uri');
    }

    protected function getAiResponse(array $payload): array
    {
        $payload = $this->prepareRequestPayload($payload);

        $response = $this->makeHttpRequest($payload);

        if (empty($response)) {
            throw new \Exception('XAI Ai no response received.');

            $response = $this->mockAiResponse($payload);
        }

        return [
            'next_question' => $response['next_question'] ?? null,
            'finished' => $response['finished'] ?? false,
            'level' => $response['level'] ?? null,
            'description' => $response['description'] ?? null,
        ];
    }

    protected function mockAiResponse(array $payload): array
    {
        static $step = 0;
        $step++;

        $language = $payload['language'];
        $questions = [
            'english' => [
                'Tell me about yourself.',
                'Describe your favorite book.',
                'What do you do in your free time?',
            ],
            'polish' => [
                'Opowiedz mi o sobie.',
                'Opisz swoją ulubioną książkę.',
                'Co robisz w wolnym czasie?',
            ],
        ];

        $langQuestions = $questions[$language] ?? $questions['english']; // Domyślnie angielski

        if ($step < count($langQuestions)) {
            return ['next_question' => $langQuestions[$step]];
        }

        $levels = [
            'english' => ['B2', 'Good fluency with minor errors.'],
            'polish' => ['B2', 'Dobra płynność z drobnymi błędami.'],
        ];

        $levelData = $levels[$language] ?? $levels['english'];

        return [
            'finished' => true,
            'level' => $levelData[0],
            'description' => $levelData[1],
        ];
    }

    private function prepareRequestPayload(array $payload): array
    {
        $language = ucfirst($payload['language']);
        $message = $payload['message'] ?? '';

        return [
            'messages' => [
                [
                    'role' => 'system',
                    'content' => "You are asked to determine the level of $language proficiency. Based on the answers provided by the user, you will determine the level in the CEFR scale, i.e. A1 - Beginner, A2 - Elementary, B1 - Intermediate, B2 - Upper intermediate, C1 - Advanced, C2 - Proficient. The question wha",
                ],
                [
                    'role' => 'user',
                    'content' => $message,
                ]
            ],
            'model' => 'grok-3',
        ];
    }

    protected function makeHttpRequest(array $body): array
    {
        $config = config('ai.providers.xai');
        $apiKey = $config['api_key'] ?? '';

        try {
            $response = $this->httpClient->post($this->getEndpoint(), [
                'headers' => [
                    'Authentication' => 'Bearer ' . $apiKey,
                    'anthropic-version' => $config['version'],
                    'Content-Type' => 'application/json',
                ],
                'json' => $body,
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            \Log::error("Xai API error: " . $e->getMessage());
            return $this->mockAiResponse($body);
        }
    }
}
