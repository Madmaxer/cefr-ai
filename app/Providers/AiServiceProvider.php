<?php

namespace App\Providers;

use App\Services\AiProviders\ClaudeProvider;
use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;
use App\Contracts\AiProvider;
use App\Services\AiProviders\XaiProvider;
use App\Services\AiProviders\OpenAiProvider;
use App\Services\AiProviders\GoogleProvider;
use Illuminate\Contracts\Support\DeferrableProvider;

class AiServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register services.
     */
    public function register()
    {
        $this->app->singleton(AiProvider::class, function ($app) {
            $provider = config('ai.provider', 'claude');

            $client = new Client(['timeout' => 10.0]);

            return match ($provider) {
                'openai' => new OpenAiProvider($client),
                'google' => new GoogleProvider($client),
                'xai' => new XaiProvider($client),
                default => new ClaudeProvider($client)
            };
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot()
    {
        //
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides()
    {
        return [AiProvider::class];
    }
}
