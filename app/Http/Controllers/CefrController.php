<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Facades\AiProviderFacade;
use App\Models\LanguageTest;

class CefrController extends Controller
{
    public function __invoke(Request $request, $candidateId)
    {
        $candidateResponse = $request->input('response');
        $language = $request->input('language', 'english');

        $payload = [
            'candidate_id' => $candidateId,
            'message' => $candidateResponse,
            'context' => 'cefr_level_test',
            'language' => $language,
        ];

        $botResponse = AiProviderFacade::sendRequest($payload);

        if ($botResponse['finished']) {
            LanguageTest::create([
                'candidate_id' => $candidateId,
                'language' => $language,
                'level' => $botResponse['level'],
                'description' => $botResponse['description'],
                'tested_at' => now(),
            ]);
        }

        return response()->json($botResponse);
    }
}
