<?php

namespace App\Services\AiProviders;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

final class ClaudeProvider extends AbstractAiProvider
{
    public function __construct(Client $httpClient)
    {
        parent::__construct($httpClient, 'claude');
    }

    protected function getAiResponse(array $payload): array
    {
        static $questionHistory = [];
        static $step = 0;
        static $needsMoreDetails = false;

        $config = config('ai.providers.claude');
        $language = $payload['language'] ?? 'english';
        $userResponse = $payload['message'] ?? '';

        if ($step > 0 && $userResponse) {
            $lastQuestion = $step === 1 ? 'Tell me about yourself.' : $questionHistory[$step - 2]['next_question'];
            $questionHistory[] = [
                'question' => $lastQuestion,
                'response' => $userResponse,
            ];
        }

        // Tworzenie parametru system
        $systemPrompt = "You are a bot that determines the level of {$language}. Based on the answers provided by the user, you will determine the level on the CEFR scale (A1 - Beginner, A2 - Elementary, B1 - Intermediate, B2 - Upper intermediate, C1 - Advanced, C2 - Proficient).\n\n";

        if ($step === 0) {
            $systemPrompt .= "Start by asking the user: 'Tell me about yourself.'.";
        } else {
            $systemPrompt .= "Previous questions and answers:\n";
            foreach ($questionHistory as $entry) {
                $systemPrompt .= "- Question: '{$entry['question']}'\n  Response: '{$entry['response']}'\n";
            }

            $wordCount = str_word_count($userResponse);
            if ($needsMoreDetails || ($wordCount > 0 && $wordCount <= 5)) {
                $systemPrompt .= "The user's last response was too short or needs more details to assess their level. Ask them to elaborate on their previous answer without counting this as a new question. Use '[More Details]' to indicate this.";
                $needsMoreDetails = true;
            } else {
                $systemPrompt .= "Based on the user's responses so far, generate a relevant follow-up question to assess their {$language} level. Use '[Question]' to indicate this is a new question.";
                if ($step === 5) {
                    $systemPrompt .= " This is the 5th question. After evaluating the response, provide a final CEFR level assessment ending with 'Score: [level]' (e.g., 'Score: A2').";
                }
            }
        }

        $body = [
            'model' => $config['model'],
            'max_tokens' => 1000,
            'temperature' => 0.7,
            'system' => $systemPrompt,
            'messages' => [
                ['role' => 'user', 'content' => $userResponse],
            ],
        ];

        $response = $this->makeHttpRequest($body);
        $text = $response['content'][0]['text'] ?? '';

        $isMoreDetailsRequest = str_contains($text, '[More Details]');
        if ($isMoreDetailsRequest) {
            $needsMoreDetails = true;
        } elseif (!$needsMoreDetails) {
            $step++;
            $needsMoreDetails = false;
        } else {
            $needsMoreDetails = false;
        }

        if ($step < 6) {
            return ['text' => $text];
        }

        preg_match('/Score: (A[12]|B[12]|C[12])/', $text, $matches);
        $level = $matches[1] ?? 'A1';

        return [
            'text' => $text,
            'level' => $level,
            'finished' => true,
        ];
    }

    public function getEndpoint(): string
    {
        return config('ai.providers.claude.endpoint');
    }

    protected function makeHttpRequest(array $body): array
    {
        $config = config('ai.providers.claude');
        $apiKey = $config['api_key'] ?? '';

        try {
            $response = $this->httpClient->post($this->getEndpoint(), [
                'headers' => [
                    'x-api-key' => $apiKey,
                    'anthropic-version' => $config['version'],
                    'Content-Type' => 'application/json',
                ],
                'json' => $body,
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            \Log::error("Claude API error: " . $e->getMessage());
            return $this->mockAiResponse($body);
        }
    }
}
