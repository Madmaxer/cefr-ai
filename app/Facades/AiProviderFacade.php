<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class AiProviderFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \App\Contracts\AiProvider::class;
    }
}
