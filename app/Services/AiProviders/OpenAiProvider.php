<?php

namespace App\Services\AiProviders;

use GuzzleHttp\Client;
use App\Contracts\AiProvider;

class OpenAiProvider implements AiProvider
{
    protected $client;
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = config('ai.providers.openai.api_key');
        $this->client = new Client(['base_uri' => config('ai.providers.openai.base_uri')]);
    }

    public function sendRequest(array $payload): array
    {
        $openAiPayload = [
            'model' => 'gpt-4',
            'messages' => [
                ['role' => 'user', 'content' => json_encode($payload)],
            ],
        ];

        $response = $this->client->post($this->getEndpoint(), [
            'headers' => [
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
            ],
            'json' => $openAiPayload,
        ]);

        $data = json_decode($response->getBody(), true);

        return json_decode($data['choices'][0]['message']['content'], true);
    }

    public function getEndpoint(): string
    {
        return 'chat/completions';
    }
}

