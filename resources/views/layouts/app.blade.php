<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'YuSum') }} — AI YouTube Summarizer</title>
    <meta name="description" content="YuSum uses Advanced AI to summarize any YouTube video in seconds. Get key insights, lectures notes, and summaries instantly.">
    <meta name="keywords" content="youtube summarizer, ai summary, gemini ai, video transcript, youtube to text">
    
    <!-- Open Graph / Social Media -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="YuSum — AI YouTube Summarizer">
    <meta property="og:description" content="Summarize any YouTube video instantly with AI. Save hours of watching.">
    <meta property="og:image" content="{{ asset('og-image.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #000; }
        .hero-gradient { background: radial-gradient(circle at 50% 50%, #111 0%, #000 100%); }
        .glass { background: rgba(255, 255, 255, 0.03); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.1); }
        @keyframes blob {
            0% { transform: translate(0px, 0px) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
            100% { transform: translate(0px, 0px) scale(1); }
        }
        .animate-blob { animation: blob 10s infinite alternate; }
        .animation-delay-2000 { animation-delay: 2s; }
        .animation-delay-4000 { animation-delay: 4s; }
    </style>
    @livewireStyles
</head>
<body class="hero-gradient text-white min-h-screen antialiased relative overflow-x-hidden">
    <!-- Premium Dynamic Background Elements -->
    <div class="fixed inset-0 pointer-events-none z-[-1]">
        <div class="absolute top-[-10%] left-[-10%] w-[40vw] h-[40vw] bg-purple-600/20 rounded-full blur-[120px] mix-blend-screen animate-blob"></div>
        <div class="absolute top-[20%] right-[-10%] w-[35vw] h-[35vw] bg-orange-600/20 rounded-full blur-[120px] mix-blend-screen animate-blob animation-delay-2000"></div>
        <div class="absolute bottom-[-10%] left-[20%] w-[45vw] h-[45vw] bg-red-600/20 rounded-full blur-[120px] mix-blend-screen animate-blob animation-delay-4000"></div>
    </div>
    
    <nav class="border-b border-white/5 bg-black/40 backdrop-blur-xl sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2 group">
                    <div class="w-8 h-8 bg-gradient-to-br from-orange-500 to-red-600 rounded-lg shadow-lg shadow-orange-500/20 group-hover:scale-105 transition-transform"></div>
                    <span class="text-xl font-bold tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-white to-gray-400">YuSum</span>
                </a>
                <div class="flex items-center gap-8">
                    <div class="hidden md:flex items-center gap-6 text-sm font-medium text-gray-400">
                        <a href="{{ route('dashboard') }}" class="hover:text-white transition-colors">Dashboard</a>
                        @if(Auth::user()->is_admin)
                            <a href="{{ route('admin.dashboard') }}" class="px-3 py-1 bg-purple-500/10 border border-purple-500/20 rounded-full text-purple-400 hover:bg-purple-500/20 transition-all font-bold tracking-wider text-xs">ADMIN PANEL</a>
                        @endif
                        <a href="#" class="hover:text-white transition-colors">Pricing</a>
                    </div>
                    <div class="flex items-center gap-4 pl-6 border-l border-white/10">
                        <div class="flex flex-col items-end hidden sm:flex">
                            <span class="text-sm font-semibold text-gray-200">{{ Auth::user()->name }}</span>
                            <span class="text-[10px] text-orange-500 font-bold uppercase tracking-widest">{{ Auth::user()->credits }} Credits</span>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="p-2 rounded-lg hover:bg-white/5 text-gray-500 hover:text-white transition-all">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <main class="py-12">
        {{ $slot }}
    </main>

    @livewireScripts
    @vite(['resources/js/app.js'])
</body>
</html>
