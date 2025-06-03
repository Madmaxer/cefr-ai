<?php

namespace App\Http\Controllers;

use App\Contracts\AiProvider;
use App\Enums\Language;
use App\Models\LanguageTest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CefrController extends Controller
{
    public function __construct(private readonly AiProvider $provider)
    {
    }

    public function showTest(string $language): \Illuminate\View\View
    {
        $languageInput = strtolower($language);
        $validLanguage = in_array($languageInput, Language::values())
            ? Language::from($languageInput)
            : Language::ENGLISH;

        return view('cefr', [
            'language' => $validLanguage->value,
            'speechApiCode' => $validLanguage->getSpeechApiCode(),
        ]);
    }

    public function languageTest(Request $request, string $userId): JsonResponse
    {
        $userResponse = $request->input('response');
        $languageInput = strtolower($request->input('language', Language::ENGLISH->value));

        $language = in_array($languageInput, Language::values())
            ? Language::from($languageInput)
            : Language::ENGLISH;

        $payload = [
            'user_id' => $userId,
            'message' => $userResponse,
            'language' => $language->value,
        ];

        $botResponse = $this->provider->sendRequest($payload);

        if ($botResponse['finished'] ?? false) {
            $validated = $request->validate([
                'user_id' => 'required|uuid|exists:users,id',
                'language' => 'required|string|in:' . implode(',', Language::values()),
                'level' => 'required|string',
                'description' => 'required|string',
                'tested_at' => 'required|date',
            ], [
                'user_id' => $userId,
                'language' => $language->value,
                'level' => $botResponse['level'],
                'description' => $botResponse['description'],
                'tested_at' => now()->toDateTimeString(),
            ]);

            LanguageTest::create($validated);
        }

        return response()->json($botResponse);
    }
}
