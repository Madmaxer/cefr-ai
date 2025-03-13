<?php

namespace App\Services\AiProviders;

use GuzzleHttp\Client;
use App\Contracts\AiProvider;

class GoogleProvider implements AiProvider
{
    protected $client;

    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = config('ai.providers.google.api_key');
        $this->client = new Client(['base_uri' => config('ai.providers.google.base_uri')]);
    }

    public function sendRequest(array $payload): array
    {
        $response = $this->client->post($this->getEndpoint(), [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'query' => ['key' => $this->apiKey],
            'json' => ['contents' => [['parts' => [['text' => json_encode($payload)]]]]],
        ]);

        $data = json_decode($response->getBody(), true);
        return json_decode($data['candidates'][0]['content']['parts'][0]['text'], true);
    }

    public function getEndpoint(): string
    {
        return 'v1beta/models/grok:generate';
    }
}

