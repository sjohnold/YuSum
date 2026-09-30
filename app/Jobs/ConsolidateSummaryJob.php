<?php

namespace App\Jobs;

use App\Models\Summary;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use App\Services\OpenRouterService;

class ConsolidateSummaryJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $summaryId,
        public string $promptMode = 'general'
    ) {}

    /**
     * Execute the job.
     */
    public function handle(OpenRouterService $openRouter): void
    {
        $summary = Summary::find($this->summaryId);
        if (!$summary) return;

        $chunks = $summary->chunks()->orderBy('index')->get();
        $combinedSummaries = $chunks->pluck('summary')->filter()->join("\n\n---\n\n");

        $systemPrompt = $this->getSystemPrompt($this->promptMode);

        try {
            $result = $openRouter->generateContent($systemPrompt, $combinedSummaries);

            $finalSummary = $result['text'];
            $totalTokens = $chunks->sum('tokens_used') + $result['tokens'];

            Log::info("Consolidating summary for ID {$this->summaryId}. Total tokens: {$totalTokens}");

            $summary->update([
                'summary' => $finalSummary,
                'status' => 'completed',
                'tokens_used' => $totalTokens
            ]);

            event(new \App\Events\SummaryUpdated($summary));

            // Cleanup chunks to save storage
            $summary->chunks()->delete();

        } catch (\Exception $e) {
            $summary->update(['status' => 'failed']);
            Log::error("Consolidation failed for summary {$this->summaryId}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Handle a job failure — refund the user's credit.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("ConsolidateSummaryJob permanently failed for summary {$this->summaryId}: " . $exception->getMessage());

        \Illuminate\Support\Facades\DB::transaction(function () {
            $summary = Summary::where('id', $this->summaryId)
                ->lockForUpdate()
                ->first();
            
            if ($summary && in_array($summary->status, ['pending', 'processing'])) {
                $user = \App\Models\User::lockForUpdate()->find($summary->user_id);
                if ($user) {
                    $user->increment('credits');
                }
                $summary->update(['status' => 'failed']);
                event(new \App\Events\SummaryUpdated($summary));
            }
        });
    }

    /**
     * Get specific prompt for final consolidation based on mode
     */
    private function getSystemPrompt(string $mode): string
    {
        $prompts = [
            'general' => 'You are a professional editor. Below are summaries of different parts of a video. Combine them into a single, cohesive, and high-quality final summary with key insights and action items in Markdown format. Do not hallucinate.',
            'instruction' => 'You are a technical writer. Below are extracted steps from different parts of a video. Combine them into a single, cohesive, and chronological Step-by-Step guide in Markdown format. Include a list of required tools if mentioned. Do not hallucinate.',
            'lecture' => 'You are a university professor. Below are extracted notes from different parts of a lecture. Combine them into a single, structured academic document in Markdown format. Use clear headings for main topics, subheadings for details, and highlight key concepts. Do not hallucinate.',
            'key_points' => 'You are a data analyst. Below are extracted facts from a video. Combine them into a single, highly concise, bulleted list of Key Takeaways in Markdown format. Remove all filler content and focus purely on actionable facts and figures. Do not hallucinate.',
            'eli5' => 'You are a friendly teacher. Below are summarized parts of a video. Combine them and explain the overall core message as if you were talking to a 5-year-old child. Use a very warm tone, simple words, and an analogy if it helps. Format in Markdown.',
        ];

        return $prompts[$mode] ?? $prompts['general'];
    }
}
