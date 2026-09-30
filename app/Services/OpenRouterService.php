<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;
use App\Models\AppLog;

class OpenRouterService
{
    /**
     * Generate content via OpenRouter API (OpenAI-compatible).
     * 
     * @param string $systemPrompt The system instruction
     * @param string $userPrompt The user message (transcript text)
     * @return array ['text' => string, 'tokens' => int]
     * @throws Exception
     */
    public function generateContent(string $systemPrompt, string $userPrompt): array
    {
        $apiKey = config('services.openrouter.key');
        $model = config('services.openrouter.model', 'openrouter/auto');
        
        if (empty($apiKey)) {
            throw new Exception("OPENROUTER_API_KEY is missing from environment variables.");
        }

        $payload = [
            'model' => $model,
            'temperature' => (float) config('services.openrouter.temperature', 0.2),
            'max_tokens' => (int) config('services.openrouter.max_tokens', 4000),
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
        ];

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$apiKey}",
            'Content-Type' => 'application/json',
            'HTTP-Referer' => config('app.url', 'https://yusum.onrender.com'),
            'X-OpenRouter-Title' => 'YuSum',
        ])->connectTimeout(10)
            ->timeout(120)
            ->retry(3, 750, throw: false)
            ->post('https://openrouter.ai/api/v1/chat/completions', $payload);

        if ($response->failed()) {
            AppLog::create([
                'level' => 'error',
                'action' => 'openrouter_api_error',
                'message' => 'OpenRouter API call failed',
                'details' => [
                    'status' => $response->status(),
                    'model' => $model,
                ]
            ]);
            throw new Exception("Summary provider failed with HTTP {$response->status()}.");
        }

        $data = $response->json();
        
        // Validate response structure
        if (!isset($data['choices'][0]['message']['content'])) {
            AppLog::create([
                'level' => 'error',
                'action' => 'openrouter_format_error',
                'message' => 'Unexpected response format from OpenRouter',
                'details' => [
                    'model' => $model,
                    'request_id' => $response->header('x-request-id'),
                ]
            ]);
            throw new Exception("Unexpected OpenRouter API response format.");
        }

        $text = trim((string) $data['choices'][0]['message']['content']);
        if ($text === '') {
            throw new Exception('Summary provider returned an empty response.');
        }
        
        // Extract token usage
        $tokensUsed = $data['usage']['total_tokens'] 
            ?? (($data['usage']['prompt_tokens'] ?? 0) + ($data['usage']['completion_tokens'] ?? 0));

        if ($tokensUsed === 0) {
            // Rough approximation if API doesn't return usage
            $tokensUsed = (int) (mb_strlen($text) / 4);
        }

        AppLog::create([
            'level' => 'info',
            'action' => 'openrouter_api_success',
            'message' => 'OpenRouter API call successful',
            'details' => [
                'model' => $data['model'] ?? $model,
                'tokens' => $tokensUsed,
            ]
        ]);

        return [
            'text' => $text,
            'tokens' => (int) $tokensUsed
        ];
    }
}
