<?php

namespace App\Contracts;

interface AiProvider
{
    public function sendRequest(array $payload): array;
    public function getEndpoint(): string;
}
