<?php

namespace App\Jobs;

use App\Models\Summary;
use App\Services\TranscriptService;
use App\Services\OpenRouterService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use App\Models\AppLog;

class ProcessVideoJob implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    public $timeout = 600;

    public $backoff = [30, 120, 300];

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $videoId,
        public int $userId,
        public string $promptMode = 'general',
        public string $targetLanguage = 'ru'
    ) {}

    /**
     * Execute the job.
     */
    public function handle(TranscriptService $transcriptService, OpenRouterService $openRouter): void
    {
        $summary = Summary::where('video_id', $this->videoId)
            ->where('user_id', $this->userId)
            ->where('prompt_mode', $this->promptMode)
            ->where('language', $this->targetLanguage)
            ->first();

        if (!$summary) {
            return;
        }

        try {
            $summary->update(['status' => 'processing']);

            AppLog::create([
                'user_id' => $this->userId,
                'level' => 'info',
                'action' => 'process_video_start',
                'message' => "Starting processing for video: {$this->videoId} with language: {$this->targetLanguage}",
            ]);

            // 1. Fetch Transcript/Metadata via Apify
            // We'll modify TranscriptService to return an array with text, title, and description
            $data = $transcriptService->fetchTranscriptData($this->videoId);
            $transcript = $data['transcript'] ?? '';
            $videoTitle = $data['title'] ?? 'Unknown Title';
            $videoDescription = $data['description'] ?? '';

            if (empty($transcript)) {
                AppLog::create([
                    'user_id' => $this->userId,
                    'level' => 'warning',
                    'action' => 'transcript_missing',
                    'message' => "Transcript not found for {$this->videoId}. Falling back to metadata.",
                ]);
                
                $userPrompt = "The video transcript is unavailable. Please provide a summary based on the following metadata:\n\nTitle: {$videoTitle}\nDescription: {$videoDescription}";
            } else {
                AppLog::create([
                    'user_id' => $this->userId,
                    'level' => 'info',
                    'action' => 'transcript_fetched',
                    'message' => "Transcript fetched successfully",
                    'details' => ['length' => mb_strlen($transcript)]
                ]);
                $summary->update(['transcript' => $transcript]);
                $userPrompt = "Please analyze the following transcript of the video titled '{$videoTitle}':\n\n{$transcript}";
            }

            // 2. Generate Summary via OpenRouter
            $systemPrompt = $this->getSystemPrompt($this->promptMode, $this->targetLanguage);
            
            $result = $openRouter->generateContent($systemPrompt, $userPrompt);

            $summary->update([
                'summary' => $result['text'],
                'status' => 'completed',
                'tokens_used' => $result['tokens']
            ]);
            
            AppLog::create([
                'user_id' => $this->userId,
                'level' => 'info',
                'action' => 'summary_generated',
                'message' => "Summary generated successfully",
                'details' => ['tokens' => $result['tokens']]
            ]);

            event(new \App\Events\SummaryUpdated($summary));

        } catch (\Throwable $e) {
            AppLog::create([
                'user_id' => $this->userId,
                'level' => 'error',
                'action' => 'process_video_error',
                'message' => $e->getMessage(),
                'details' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ]);
            
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("ProcessVideoJob permanently failed for video {$this->videoId}: " . $exception->getMessage());
        
        \Illuminate\Support\Facades\DB::transaction(function () {
            $summary = Summary::where('video_id', $this->videoId)
                ->where('user_id', $this->userId)
                ->where('prompt_mode', $this->promptMode)
                ->where('language', $this->targetLanguage)
                ->lockForUpdate()
                ->first();
            
            if ($summary && in_array($summary->status, ['pending', 'processing'])) {
                $user = \App\Models\User::lockForUpdate()->find($this->userId);
                if ($user) {
                    $user->increment('credits');
                }
                
                $summary->update(['status' => 'failed']);
                event(new \App\Events\SummaryUpdated($summary));
            }
        });
    }

    /**
     * Get specific prompt based on mode and language
     */
    private function getSystemPrompt(string $mode, string $lang): string
    {
        $languages = [
            'ru' => 'Твой ответ ДОЛЖЕН быть только на РУССКОМ языке.',
            'en' => 'Your response MUST be in ENGLISH only.',
            'sr' => 'Твој одговор МОРА бити само на СРПСКОМ језику.',
        ];

        $targetLang = $languages[$lang] ?? $languages['ru'];
        $langInstruction = "\n\nВАЖНО: {$targetLang} Используй Markdown для форматирования.";
        
        $prompts = [
            'general' => 'Ты — эксперт по суммаризации видео. Твоя задача — составить подробное, связное и содержательное резюме предоставленного транскрипта. Выдели главную тему, ключевые аргументы и итоговые выводы.' . $langInstruction,
            'instruction' => 'Ты — технический писатель. Извлеки пошаговые инструкции из транскрипта видео. Структурируй ответ в виде четких заголовков, нумерованных списков и перечня необходимых инструментов/материалов, если они упоминаются. Будь предельно точен.' . $langInstruction,
            'lecture' => 'Ты — помощник профессора. Составь конспект образовательной лекции на основе транскрипта. Используй четкую иерархию (H1, H2, H3). Выдели ключевые определения, концепции и основные тезисы. Сохраняй академический тон.' . $langInstruction,
            'key_points' => 'Ты — ассистент руководителя. Прочитай транскрипт и извлеки ТОЛЬКО самые важные факты, цифры и ключевые выводы. Представь их в виде краткого маркированного списка. Убери всё лишнее, истории и "воду".' . $langInstruction,
            'eli5' => 'Ты — дружелюбный учитель. Объясни суть этого видео так, как будто рассказываешь 5-летнему ребенку. Используй простые аналогии, базовые слова и теплый тон. Сделай объяснение коротким и понятным.' . $langInstruction,
        ];

        return $prompts[$mode] ?? $prompts['general'];
    }
}
