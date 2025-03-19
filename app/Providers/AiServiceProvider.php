<?php

namespace App\Providers;

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
            $provider = config('ai.provider', 'xai');

            return match ($provider) {
                'openai' => new OpenAiProvider(),
                'google' => new GoogleProvider(),
                default => new XaiProvider(),
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
