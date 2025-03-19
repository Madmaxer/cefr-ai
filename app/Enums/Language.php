<?php

namespace App\Enums;

enum Language: string
{
    case ENGLISH = 'english';
    case SPANISH = 'spanish';
    case FRENCH = 'french';
    case GERMAN = 'german';
    case ITALIAN = 'italian';
    case PORTUGUESE = 'portuguese';
    case RUSSIAN = 'russian';
    case POLISH = 'polish';
    case DUTCH = 'dutch';
    case SWEDISH = 'swedish';

    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }

    public function getSpeechApiCode(): string
    {
        return match ($this) {
            self::ENGLISH => 'en-US',
            self::SPANISH => 'es-ES',
            self::FRENCH => 'fr-FR',
            self::GERMAN => 'de-DE',
            self::ITALIAN => 'it-IT',
            self::PORTUGUESE => 'pt-PT',
            self::RUSSIAN => 'ru-RU',
            self::POLISH => 'pl-PL',
            self::DUTCH => 'nl-NL',
            self::SWEDISH => 'sv-SE',
            default => 'en-US', // Domyślnie angielski
        };
    }
}
