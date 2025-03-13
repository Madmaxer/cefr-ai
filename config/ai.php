<?php

return [
    'provider' => env('AI_PROVIDER', 'xai'),

    'providers' => [
        'xai' => [
            'api_key' => env('XAI_API_KEY'),
            'base_uri' => env('XAI_BASE_URI', 'https://api.x.ai/v1/'),
        ],
        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
            'base_uri' => env('OPENAI_BASE_URI', 'https://api.openai.com/v1/'),
        ],
        'google' => [
            'api_key' => env('GOOGLE_API_KEY'),
            'base_uri' => env('GOOGLE_BASE_URI', 'https://generativelanguage.googleapis.com/'),
        ],
    ],
];
