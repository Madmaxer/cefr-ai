<?php

namespace App\Services\AiProviders;

use GuzzleHttp\Client;
use App\Contracts\AiProvider;

class XaiProvider implements AiProvider
{
    protected $client;

    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = config('ai.providers.xai.api_key');
        $this->client = new Client(['base_uri' => config('ai.providers.xai.base_uri')]);
    }

    public function sendRequest(array $payload): array
    {
        $response = $this->client->post($this->getEndpoint(), [
            'headers' => [
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
            ],
            'json' => $payload,
        ]);

        return json_decode($response->getBody(), true);
    }

    public function getEndpoint(): string
    {
        return 'grok'; // Endpoint dla xAI
    }
}
