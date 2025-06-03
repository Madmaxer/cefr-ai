<?php

return [
    'provider' => env('AI_PROVIDER', 'xai'),

    'providers' => [
        'xai' => [
            'api_key' => env('XAI_API_KEY'),
            'base_uri' => env('XAI_BASE_URI', 'https://api.x.ai/v1/'),
            'version' => 'grok-2',
        ],
        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
            'base_uri' => env('OPENAI_BASE_URI', 'https://api.openai.com/v1/'),
        ],
        'google' => [
            'api_key' => env('GOOGLE_API_KEY'),
            'base_uri' => env('GOOGLE_BASE_URI', 'https://generativelanguage.googleapis.com/'),
        ],
        'claude' => [
            'api_key' => env('CLAUDE_API_KEY'),
            'base_uri' => env('CLAUDE_BASE_URI', 'https://api.anthropic.com/v1/'),
            'version' => env('CLAUDE_VERSION', '2023-06-01'),
            'model' => env('CLAUDE_MODEL', 'claude-3-opus-20240229'),
        ],
    ],
];
