<?php

namespace App\Services\AiProviders;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

final class GoogleProvider extends AbstractAiProvider
{
    public function __construct(Client $httpClient)
    {
        parent::__construct($httpClient, 'google');
    }

    public function getEndpoint(): string
    {
        return config('ai.providers.google.endpoint');
    }

    protected function getAiResponse(array $payload): array
    {
        $body = [
            'prompt' => $payload['message'],
            'language' => $payload['language'],
            'context' => $payload['context'],
        ];

        $response = $this->makeHttpRequest($body);

        return [
            'next_question' => $response['next_question'] ?? null,
            'finished' => $response['finished'] ?? false,
            'level' => $response['level'] ?? 'C1',
            'description' => $response['description'] ?? 'Advanced proficiency, excellent command.',
        ];
    }

    protected function makeHttpRequest(array $body): array
    {
        $config = config('ai.providers.google');
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
            \Log::error("Google API error: " . $e->getMessage());
            return $this->mockAiResponse($body);
        }
    }
}
