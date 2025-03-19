<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Facades\AiProviderFacade;
use App\Models\LanguageTest;
use App\Enums\Language;

class CefrController extends Controller
{
    public function showTest($language)
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

    public function languageTest(Request $request, $userId)
    {
        $userResponse = $request->input('response');
        $languageInput = strtolower($request->input('language', Language::ENGLISH->value));

        $language = in_array($languageInput, Language::values())
            ? Language::from($languageInput)
            : Language::ENGLISH;

        $payload = [
            'user_id' => $userId,
            'message' => $userResponse,
            'context' => 'cefr_level_test',
            'language' => $language->value,
        ];

        $botResponse = AiProviderFacade::sendRequest($payload);

        if ($botResponse['finished']) {
            $this->storeSummary(new Request([
                'user_id' => $userId,
                'language' => $language->value,
                'level' => $botResponse['level'],
                'description' => $botResponse['description'],
                'tested_at' => now()->toDateTimeString(),
            ]));
        }

        return response()->json($botResponse);
    }

    public function storeSummary(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|uuid|exists:users,id',
            'language' => 'required|string|in:' . implode(',', Language::values()),
            'level' => 'required|string',
            'description' => 'required|string',
            'tested_at' => 'required|date',
        ]);

        $test = LanguageTest::create($validated);

        return response()->json([
            'message' => 'Summary stored successfully',
            'test_id' => $test->id,
        ], 201);
    }
}
