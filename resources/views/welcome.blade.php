<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>YouTube Summarizer - AI-Powered Video Insights</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .glass { background: rgba(255, 255, 255, 0.03); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.1); }
        .hero-gradient { background: radial-gradient(circle at 50% 50%, #1a1a1a 0%, #000000 100%); }
        .accent-gradient { background: linear-gradient(135deg, #FF3D00 0%, #FF9100 100%); }
    </style>
</head>
<body class="hero-gradient text-white min-h-screen flex flex-col items-center justify-center p-6 antialiased">
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden z-0 pointer-events-none opacity-20">
        <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-orange-600 blur-[120px] rounded-full"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-red-600 blur-[120px] rounded-full"></div>
    </div>

    <div class="z-10 w-full max-w-2xl text-center space-y-8">
        <div class="space-y-4">
            <h1 class="text-5xl md:text-7xl font-bold tracking-tight">
                Summarize <span class="text-transparent bg-clip-text accent-gradient">YouTube</span> <br>in seconds.
            </h1>
            <p class="text-xl text-gray-400 font-light max-w-lg mx-auto">
                Extract key insights, action items, and main ideas from any YouTube video using advanced AI.
            </p>
        </div>

        <div class="flex flex-col items-center gap-6 pt-4">
            @auth
                <a href="{{ url('/dashboard') }}" class="px-8 py-4 rounded-2xl accent-gradient font-semibold text-lg hover:scale-105 transition-transform shadow-lg shadow-orange-900/20">
                    Go to Dashboard
                </a>
            @else
                <a href="{{ route('login.google') }}" class="glass group flex items-center gap-4 px-8 py-4 rounded-2xl hover:bg-white/5 transition-all duration-300">
                    <svg class="w-6 h-6" viewBox="0 0 24 24">
                        <path fill="currentColor" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
                        <path fill="currentColor" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                        <path fill="currentColor" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />
                        <path fill="currentColor" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
                    </svg>
                    <span class="text-lg font-medium group-hover:translate-x-1 transition-transform">Continue with Google</span>
                </a>
                <p class="text-sm text-gray-500">Get 3 free credits upon first sign-in.</p>

                @if (app()->isLocal())
                    <form method="POST" action="{{ route('dev.login') }}" class="w-full max-w-sm">
                        @csrf
                        <button type="submit" class="w-full rounded-2xl border border-orange-500/30 bg-orange-500/10 px-8 py-4 text-lg font-medium text-orange-100 transition-all duration-300 hover:border-orange-400/50 hover:bg-orange-500/20">
                            Continue as Local Admin
                        </button>
                    </form>
                    <p class="text-xs text-gray-500">Local-only shortcut for <span class="text-gray-300">admin@example.com</span>.</p>
                @endif
            @endauth
        </div>
    </div>

    <footer class="absolute bottom-8 text-gray-600 text-sm">
        &copy; {{ date('Y') }} YouTube Summarizer SaaS. All rights reserved.
    </footer>
</body>
</html>
