<?php

namespace App\Livewire;

use App\Jobs\ProcessVideoJob;
use App\Models\Summary;
use App\Rules\ValidYouTubeUrl;
use App\Services\TranscriptService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\On;

class Dashboard extends Component
{
    public string $url = '';
    public string $promptMode = 'general';
    public string $targetLanguage = 'ru';
    public ?Summary $currentSummary = null;
    public $userId;

    /**
     * Mount the component.
     */
    public function mount()
    {
        $this->userId = Auth::id();

        $this->currentSummary = Summary::where('user_id', $this->userId)
            ->latest()
            ->first();
    }

    /**
     * Start the summarization process.
     */
    public function startSummarization(TranscriptService $transcriptService)
    {
        $this->validate([
            'url' => ['required', new ValidYouTubeUrl],
            'promptMode' => ['required', 'in:general,instruction,lecture,key_points,eli5'],
            'targetLanguage' => ['required', 'in:ru,en,sr'],
        ]);

        $user = Auth::user();
        $videoId = $transcriptService->extractVideoId($this->url);

        // All checks inside transaction to prevent race conditions
        $result = DB::transaction(function () use ($user, $videoId) {
            // Lock user row to prevent concurrent credit deduction
            $user = \App\Models\User::lockForUpdate()->find($user->id);

            // Check if already processed or processing
            $existing = Summary::where('user_id', $user->id)
                ->where('video_id', $videoId)
                ->where('prompt_mode', $this->promptMode)
                ->where('language', $this->targetLanguage)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                if ($existing->status !== 'failed') {
                    return ['summary' => $existing, 'dispatched' => false];
                }
                // Failed before — allow retry, delete old record
                $existing->delete();
            }

            if ($user->credits < 1) {
                return ['error' => 'You do not have enough credits.'];
            }

            $user->decrement('credits');

            $summary = Summary::create([
                'user_id' => $user->id,
                'video_id' => $videoId,
                'status' => 'pending',
                'prompt_mode' => $this->promptMode,
                'language' => $this->targetLanguage,
            ]);

            return ['summary' => $summary, 'dispatched' => true];
        });

        if (isset($result['error'])) {
            $this->addError('url', $result['error']);
            return;
        }

        $this->currentSummary = $result['summary'];

        if ($result['dispatched']) {
            try {
                ProcessVideoJob::dispatch($videoId, $user->id, $this->promptMode, $this->targetLanguage);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Sync queue job failed: ' . $e->getMessage());
                DB::transaction(function () use ($result, $user) {
                    $summary = Summary::whereKey($result['summary']->id)->lockForUpdate()->first();
                    if ($summary && $summary->status === 'pending') {
                        \App\Models\User::lockForUpdate()->find($user->id)?->increment('credits');
                        $summary->update(['status' => 'failed']);
                    }
                });
                $this->addError('url', 'The job could not be queued. Your credit was refunded.');
                if ($this->currentSummary) {
                    $this->currentSummary->refresh();
                }
            }
        }
    }

    public function loadSummary(int $summaryId)
    {
        $summary = Summary::where('id', $summaryId)
            ->where('user_id', Auth::id())
            ->first();

        if ($summary) {
            $this->currentSummary = $summary;
        }
    }

    /**
     * Cancel a stuck summary and refund the credit.
     */
    public function cancelSummary(int $summaryId)
    {
        DB::transaction(function () use ($summaryId) {
            $summary = Summary::where('id', $summaryId)
                ->where('user_id', Auth::id())
                ->whereIn('status', ['pending', 'processing'])
                ->lockForUpdate()
                ->first();

            if ($summary) {
                $summary->update(['status' => 'failed']);
                $user = \App\Models\User::find(Auth::id());
                $user->increment('credits');

                if ($this->currentSummary && $this->currentSummary->id === $summaryId) {
                    $this->currentSummary->refresh();
                }
            }
        });
    }

    /**
     * Delete a summary from history.
     */
    public function deleteSummary(int $summaryId)
    {
        $summary = Summary::where('id', $summaryId)
            ->where('user_id', Auth::id())
            ->first();

        if ($summary) {
            $summary->delete();
            
            if ($this->currentSummary && $this->currentSummary->id === $summaryId) {
                $this->currentSummary = null;
            }
        }
    }

    /**
     * Listen for real-time updates (Reverb).
     */
    #[On('echo-private:users.{userId},SummaryUpdated')]
    public function refreshStatus()
    {
        if ($this->currentSummary) {
            $this->currentSummary->refresh();
        }

        // Refresh user to update credits display in real-time
        auth()->user()->refresh();
    }

    public function render()
    {
        return view('livewire.dashboard', [
            'summaries' => Summary::where('user_id', Auth::id())->latest()->take(5)->get(),
        ]);
    }
}
