<?php

namespace App\Services\AiProviders;

use GuzzleHttp\Exception\RequestException;

final class OpenAiProvider extends AbstractAiProvider
{
    public function getEndpoint(): string
    {
        return config('ai.providers.openai.endpoint');
    }

    protected function getAiResponse(array $payload): array
    {
        // Format żądania dla OpenAI
        $body = [
            'model' => 'gpt-4',
            'messages' => [
                ['role' => 'system', 'content' => "You are a language test assistant for CEFR level evaluation in {$payload['language']}."],
                ['role' => 'user', 'content' => $payload['message']],
            ],
            'max_tokens' => 100,
        ];

        $response = $this->makeHttpRequest($body);

        // Normalizacja odpowiedzi OpenAI
        $content = $response['choices'][0]['message']['content'] ?? '';
        return $this->parseOpenAiResponse($content);
    }

    private function parseOpenAiResponse(string $content): array
    {
        // Przykładowa logika parsowania (dostosuj do rzeczywistych odpowiedzi)
        if (str_contains($content, 'Thank you')) {
            return [
                'finished' => true,
                'level' => 'A2',
                'description' => 'Basic understanding, limited vocabulary.',
            ];
        }
        return ['next_question' => $content];
    }

    protected function makeHttpRequest(array $body): array
    {
        $config = config('ai.providers.openai');
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
            \Log::error("Open AI API error: " . $e->getMessage());
            return $this->mockAiResponse($body);
        }
    }
}
