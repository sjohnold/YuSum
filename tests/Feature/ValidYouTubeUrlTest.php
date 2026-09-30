<?php

use App\Rules\ValidYouTubeUrl;
use Illuminate\Support\Facades\Validator;

test('it validates correct youtube urls', function ($url) {
    $rule = new ValidYouTubeUrl();
    
    $validator = Validator::make(['url' => $url], ['url' => $rule]);
    
    expect($validator->passes())->toBeTrue();
})->with([
    'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
    'https://youtube.com/watch?v=dQw4w9WgXcQ',
    'http://youtube.com/watch?v=dQw4w9WgXcQ',
    'www.youtube.com/watch?v=dQw4w9WgXcQ',
    'youtube.com/watch?v=dQw4w9WgXcQ',
    'https://youtu.be/dQw4w9WgXcQ',
    'https://www.youtube.com/shorts/dQw4w9WgXcQ',
    'https://music.youtube.com/watch?v=dQw4w9WgXcQ',
    'https://www.youtube.com/embed/dQw4w9WgXcQ',
    'https://www.youtube.com/watch?v=dQw4w9WgXcQ&t=10s',
    'https://www.youtube.com/live/dQw4w9WgXcQ?feature=share',
    'youtube.com/watch?list=PL123&v=dQw4w9WgXcQ',
]);

test('it rejects invalid youtube urls', function ($url) {
    $rule = new ValidYouTubeUrl();
    
    $validator = Validator::make(['url' => $url], ['url' => $rule]);
    
    expect($validator->fails())->toBeTrue();
})->with([
    'https://google.com',
    'https://vimeo.com/123456',
    'youtube.com/watch',
    'youtube.com/user/channel',
    'not-a-url',
    'https://youtube.com/watch?v=short',
    'https://youtube.com/watch?v=too-long-id-12345',
    'https://evil.example/youtube.com/watch?v=dQw4w9WgXcQ',
]);
