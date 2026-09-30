# Инструкция по локальному тестированию YouTube Summarizer

Ниже описан актуальный сценарий локальной проверки проекта в состоянии на сейчас.

## 1. Подготовка окружения

Если проект запускается впервые:

```bash
composer install
npm install
copy .env.example .env
php artisan key:generate
php artisan migrate
```

Что важно заполнить в `.env`:

- `OPENAI_API_KEY` — обязателен для генерации summary.
- `BROADCAST_CONNECTION=reverb` — нужен, если хотите видеть прогресс в реальном времени.
- `REVERB_*` и `VITE_REVERB_*` — нужны для локального WebSocket / Echo.
- `PADDLE_*` и `PADDLE_PRICE_ID_10_CREDITS` — нужны только для проверки оплаты.

Что важно запустить вне Laravel:

- Redis на `127.0.0.1:6379`.

Почему Redis обязателен:

- джобы `ProcessVideoJob`, `ProcessTranscriptChunkJob` и `ConsolidateSummaryJob` используют `Redis::throttle(...)`, поэтому без Redis обработка видео будет падать или зависать на ретраях.

Примечание для Windows / Laragon:

- если команда `php` не находится в `PATH`, используйте полный путь к PHP, например `E:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe`.

## 2. Запуск сервисов

Быстрый вариант:

```bash
composer run dev
```

Эта команда поднимает:

- Laravel server
- очередь
- live tail логов через `php artisan pail`
- Vite

Отдельно для real-time всё ещё нужен Reverb:

```bash
php artisan reverb:start --debug
```

Если хотите запускать всё вручную, откройте отдельные окна терминала:

1. PHP server:

   ```bash
   php artisan serve
   ```

2. Vite:

   ```bash
   npm run dev
   ```

3. Queue worker:

   ```bash
   php artisan queue:work --tries=1
   ```

4. Reverb:

   ```bash
   php artisan reverb:start --debug
   ```

5. Redis:

- запустите Redis через Laragon, Docker, WSL или любым другим удобным способом.

## 3. Быстрый вход без Google

Предпочтительный способ:

1. Откройте `http://127.0.0.1:8000/`.
2. Нажмите `Continue as Local Admin`.

Этот вход доступен только при `APP_ENV=local`.

Что делает кнопка:

- создаёт или обновляет пользователя `admin@example.com`,
- выдаёт ему `10` кредитов,
- создаёт обычную Laravel-сессию в браузере,
- переводит на `/dashboard`.

Важно:

- создание пользователя через `php artisan tinker` полезно для записи в БД, но `Auth::login($user)` внутри консоли не логинит вас в браузере.

## 4. Проверка обработки видео

1. Откройте `http://127.0.0.1:8000/dashboard`.
2. Вставьте ссылку на YouTube, например `https://www.youtube.com/watch?v=dQw4w9WgXcQ`.
3. Нажмите `Summarize`.
4. Следите за окном очереди и логами приложения.
5. Если `BROADCAST_CONNECTION=reverb` и Reverb запущен, прогресс должен обновляться в реальном времени.

Текущее ограничение:

- `TranscriptService` пока использует placeholder-транскрипт, поэтому summary сейчас проверяет пайплайн очередей, OpenAI, realtime и PDF, но не реальное извлечение субтитров с YouTube.

## 5. Проверка PDF

После завершения обработки:

1. нажмите на иконку скачивания в карточке summary;
2. проверьте, что PDF скачивается и открывается без ошибок.

## 6. Проверка Paddle

Для локальной проверки оплаты:

1. включите `PADDLE_SANDBOX=true`;
2. заполните в `.env`:
   `PADDLE_CLIENT_SIDE_TOKEN`,
   `PADDLE_API_KEY`,
   `PADDLE_WEBHOOK_SECRET`,
   `PADDLE_PRICE_ID_10_CREDITS`;
3. поднимите туннель:

   ```bash
   ngrok http 8000
   ```

4. укажите в Paddle webhook URL:
   `https://YOUR_NGROK_URL/paddle/webhook`

Что проверять:

- открывается Paddle Checkout,
- webhook приходит в Laravel,
- запись появляется в `processed_payments`,
- пользователю начисляются кредиты.

## 7. Просмотр логов

Вариант 1:

```bash
php artisan pail
```

Вариант 2 для PowerShell:

```powershell
Get-Content storage/logs/laravel.log -Wait
```

Дополнительный лог OpenAI:

```powershell
Get-Content storage/logs/openai.log -Wait
```

Сейчас в логах можно отслеживать:

- старт обработки видео,
- успешное получение транскрипта,
- ошибки OpenAI,
- входящие Paddle webhook-события.
