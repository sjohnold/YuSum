<?php

namespace App\Listeners;

use Laravel\Paddle\Events\WebhookReceived;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class HandlePaddleWebhook
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(WebhookReceived $event): void
    {
        $payload = $event->payload;
        Log::info("Paddle Webhook Received: " . $payload['event_type'], ['payload' => $payload]);

        if ($payload['event_type'] === 'transaction.completed') {
            $transactionId = $payload['data']['id'];
            $customData = $payload['data']['custom_data'] ?? [];
            $credits = (int) ($customData['credits'] ?? 0);
            $userId = $customData['user_id'] ?? null;

            if ($credits > 0 && $userId) {
                \Illuminate\Support\Facades\DB::transaction(function () use ($transactionId, $userId, $credits) {
                    // Idempotency check
                    $alreadyProcessed = \Illuminate\Support\Facades\DB::table('processed_payments')
                        ->where('paddle_id', $transactionId)
                        ->lockForUpdate() // Lock for safety
                        ->exists();

                    if ($alreadyProcessed) {
                        return;
                    }

                    $user = \App\Models\User::find($userId);

                    if ($user) {
                        $user->increment('credits', $credits);
                        
                        // Mark as processed
                        \Illuminate\Support\Facades\DB::table('processed_payments')->insert([
                            'paddle_id' => $transactionId,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                });
            }
        }
    }
}
