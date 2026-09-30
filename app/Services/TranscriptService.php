<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;
use App\Models\AppLog;
use App\Support\YouTubeUrl;

class TranscriptService
{
    /**
     * Extract YouTube Video ID from URL.
     */
    public function extractVideoId(string $url): ?string
    {
        return YouTubeUrl::videoId($url);
    }

    /**
     * Fetch transcript and metadata for a given video ID.
     * 
     * @param string $videoId
     * @return array ['transcript' => string, 'title' => string, 'description' => string]
     * @throws Exception
     */
    public function fetchTranscriptData(string $videoId): array
    {
        $token = config('services.apify.token');

        if (empty($token)) {
            AppLog::create([
                'level' => 'error',
                'action' => 'apify_missing_token',
                'message' => 'APIFY_API_TOKEN is not set in environment variables.'
            ]);
            throw new Exception("Transcript service is not configured (missing Apify Token).");
        }

        $youtubeUrl = "https://www.youtube.com/watch?v={$videoId}";

        $response = Http::connectTimeout(10)
            ->timeout(120)
            ->retry(3, 750, throw: false)
            ->withQueryParameters(['token' => $token])
            ->post('https://api.apify.com/v2/acts/starvibe~youtube-video-transcript/run-sync-get-dataset-items', [
                'youtube_url' => $youtubeUrl,
            ]);

        if ($response->failed()) {
            throw new Exception("Transcript provider failed with HTTP {$response->status()}.");
        }

        $data = $response->json();
        if (!is_array($data) || empty($data)) {
            throw new Exception("No data returned from Apify.");
        }

        $item = $data[0];
        
        // Extract transcript
        $text = $item['transcript_text'] ?? '';
        if (empty($text) && isset($item['transcript']) && is_array($item['transcript'])) {
            $text = collect($item['transcript'])->pluck('text')->implode(' ');
        }

        $title = $item['title'] ?? 'Unknown Title';
        $description = $item['description'] ?? '';

        // FALLBACK: If no text was found, use the Speech-to-Text Converter (moving_beacon-owner1/my-actor-72)
        if (empty(trim($text))) {
            AppLog::create([
                'level' => 'info',
                'action' => 'stt_fallback_started',
                'message' => "No subtitles found for {$videoId}. Starting Speech-to-Text Actor.",
            ]);

            $sttResponse = Http::connectTimeout(10)
                ->timeout(300)
                ->retry(2, 1000, throw: false)
                ->withQueryParameters(['token' => $token])
                ->post('https://api.apify.com/v2/acts/moving_beacon-owner1~my-actor-72/run-sync-get-dataset-items', [
                    'engine' => 'whisper_local',
                    'input_file_url' => $youtubeUrl, // Many STT actors support YT URLs directly via yt-dlp
                    'language' => '', // Empty string means Auto-detect language
                    'whisper_model' => 'small', // Small is a good balance of speed and accuracy
                    'output_format' => 'txt',
                ]);

            if ($sttResponse->successful()) {
                $sttData = $sttResponse->json();
                if (is_array($sttData) && !empty($sttData)) {
                    // Depending on the exact output format, the text might be in 'FULL_RESULT.text' or as plain text in the dataset
                    $sttItem = $sttData[0];
                    if (isset($sttItem['FULL_RESULT']['text'])) {
                        $text = $sttItem['FULL_RESULT']['text'];
                    } elseif (isset($sttItem['text'])) {
                        $text = $sttItem['text'];
                    } else {
                        // Sometimes the dataset just returns a url to the file
                        $outputUrl = $sttItem['output_url'] ?? null;
                        if ($outputUrl && $this->isTrustedApifyUrl($outputUrl)) {
                            $download = Http::connectTimeout(10)->timeout(60)->retry(2, 500, throw: false)->get($outputUrl);
                            $text = $download->successful() ? $download->body() : '';
                        }
                    }
                    
                    if (!empty($text)) {
                        AppLog::create([
                            'level' => 'info',
                            'action' => 'stt_fallback_success',
                            'message' => "STT successfully transcribed {$videoId}.",
                        ]);
                    }
                }
            } else {
                AppLog::create([
                    'level' => 'error',
                    'action' => 'stt_fallback_failed',
                    'message' => "STT provider failed with HTTP {$sttResponse->status()}.",
                ]);
            }
        }

        return [
            'transcript' => trim($text),
            'title' => $title,
            'description' => $description,
        ];
    }

    /**
     * Legacy method for backward compatibility.
     */
    public function fetchTranscript(string $videoId): string
    {
        $data = $this->fetchTranscriptData($videoId);
        return $data['transcript'];
    }

    private function isTrustedApifyUrl(string $url): bool
    {
        $parts = parse_url($url);
        $host = strtolower($parts['host'] ?? '');

        return ($parts['scheme'] ?? '') === 'https'
            && ($host === 'apify.com'
                || str_ends_with($host, '.apify.com')
                || $host === 'apifyusercontent.com'
                || str_ends_with($host, '.apifyusercontent.com'));
    }
}
