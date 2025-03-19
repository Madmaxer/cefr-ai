<?php

namespace App\Services\AiProviders;

final class GoogleProvider extends AbstractAiProvider
{
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
}
