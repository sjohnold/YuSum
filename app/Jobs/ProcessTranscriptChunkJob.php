<?php

namespace App\Jobs;

use App\Models\SummaryChunk;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use App\Services\OpenRouterService;

class ProcessTranscriptChunkJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $chunkId,
        public string $promptMode = 'general'
    ) {}

    /**
     * Execute the job.
     */
    public function handle(OpenRouterService $openRouter): void
    {
        $chunk = SummaryChunk::find($this->chunkId);
        if (!$chunk) return;

        $systemPrompt = $this->getSystemPrompt($this->promptMode);

        try {
            $result = $openRouter->generateContent($systemPrompt, $chunk->content);

            $chunk->update([
                'summary' => $result['text'],
                'tokens_used' => $result['tokens']
            ]);

        } catch (\Exception $e) {
            Log::error("Chunk processing failed for ID {$this->chunkId}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get specific prompt for chunks based on mode
     */
    private function getSystemPrompt(string $mode): string
    {
        $prompts = [
            'general' => 'You are analyzing a chunk of a video transcript. Extract and summarize the main points, ignoring filler words.',
            'instruction' => 'You are analyzing a chunk of a video transcript. Extract any actionable steps, tool mentions, or instructions found in this section.',
            'lecture' => 'You are analyzing a chunk of a lecture transcript. Extract academic concepts, definitions, or arguments from this section.',
            'key_points' => 'You are analyzing a chunk of a video transcript. Extract ONLY critical facts and figures from this section.',
            'eli5' => 'You are analyzing a chunk of a video transcript. Summarize the core idea of this section in very simple terms.',
        ];

        return $prompts[$mode] ?? $prompts['general'];
    }
}
