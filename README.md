# YuSum

YuSum turns YouTube videos into structured notes. It retrieves captions or falls back to speech-to-text, then produces a summary tailored to the selected language and format.

## Features

- Supports standard YouTube links, short links, Shorts, embeds, and live-video URLs
- Five output modes: general summary, instructions, lecture notes, key points, and ELI5
- Russian, English, and Serbian output
- Caption extraction with a speech-to-text fallback
- Queue-based processing, real-time status updates, history, and credit accounting
- Retry and backoff policies for transient provider failures
- Server-side URL validation and restricted remote transcript downloads
- Paddle billing, Google authentication, Horizon, Reverb, and Sentry integrations

## Architecture

Laravel 13 and Livewire 4 provide the application layer. Long-running transcript and summarization work is executed by queued jobs. Apify supplies transcript/STT data, OpenRouter generates structured summaries, and Laravel Reverb broadcasts job status to the browser.

Credit changes and summary creation run in database transactions with row locks. Failed jobs refund credits exactly once. Provider calls use bounded timeouts, retries, sanitized errors, and validated response structures.

## Local setup

Requirements: PHP 8.3+, Composer, Node.js 20+, and SQLite or PostgreSQL.

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
```

Set `APIFY_API_TOKEN` and `OPENROUTER_API_KEY`, then start the application:

```bash
composer run dev
```

Run the queue worker separately in production:

```bash
php artisan horizon
```

## Verification

```bash
composer test
npm run build
```

External HTTP calls are faked in the automated test suite, so tests do not consume provider credits.

## License

Released under the MIT License.
