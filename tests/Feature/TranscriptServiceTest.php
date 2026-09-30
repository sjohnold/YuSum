<?php

use App\Services\TranscriptService;

test('it extracts video id from various youtube urls', function ($url, $expectedId) {
    $service = new TranscriptService();
    expect($service->extractVideoId($url))->toBe($expectedId);
})->with([
    ['https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
    ['https://youtu.be/dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
    ['https://www.youtube.com/shorts/dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
    ['https://music.youtube.com/watch?v=dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
    ['https://www.youtube.com/embed/dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
    ['https://www.youtube.com/live/dQw4w9WgXcQ?feature=share', 'dQw4w9WgXcQ'],
    ['youtube.com/watch?list=PL123&v=dQw4w9WgXcQ&t=20', 'dQw4w9WgXcQ'],
]);

test('it returns null for invalid urls', function ($url) {
    $service = new TranscriptService();
    expect($service->extractVideoId($url))->toBeNull();
})->with([
    ['https://google.com'],
    ['https://vimeo.com/123'],
    ['not-a-url'],
]);

test('fetchTranscript returns provider text', function () {
    config(['services.apify.token' => 'test-token']);
    \Illuminate\Support\Facades\Http::fake([
        'api.apify.com/*' => \Illuminate\Support\Facades\Http::response([[
            'transcript_text' => 'A deterministic transcript for this test.',
            'title' => 'Test video',
        ]]),
    ]);
    $service = new TranscriptService();
    $transcript = $service->fetchTranscript('dQw4w9WgXcQ');
    expect($transcript)->toBe('A deterministic transcript for this test.');
});
