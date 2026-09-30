<div class="w-full max-w-4xl mx-auto space-y-8 p-6">
    {{-- Credit Balance --}}
    <div class="flex justify-between items-center bg-white/5 border border-white/10 p-4 rounded-2xl backdrop-blur-md">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-orange-500/20 flex items-center justify-center text-orange-500">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <div>
                <p class="text-sm text-gray-400">Available Credits</p>
                <p class="text-xl font-bold text-white">{{ auth()->user()->credits }}</p>
            </div>
        </div>
        <button class="px-4 py-2 bg-white/10 hover:bg-white/20 rounded-xl text-sm font-medium transition-colors">
            Top Up
        </button>
    </div>

    {{-- Main Input Section --}}
    <div class="bg-white/5 border border-white/10 p-8 rounded-3xl backdrop-blur-xl shadow-2xl">
        <h2 class="text-2xl font-bold mb-6 text-white text-center">Enter YouTube URL</h2>
        
        <form wire:submit.prevent="startSummarization" class="space-y-4">
            <div class="flex flex-col md:flex-row gap-4">
                <div class="relative flex-1">
                    <input 
                        type="text" 
                        wire:model="url" 
                        placeholder="https://www.youtube.com/watch?v=..."
                        class="w-full bg-black/40 border border-white/10 rounded-2xl px-6 py-4 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-orange-500/50 transition-all"
                    >
                </div>
                <div class="relative">
                    <select 
                        wire:model="promptMode"
                        class="w-full md:w-56 bg-black/40 border border-white/10 rounded-2xl px-4 py-4 text-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500/50 transition-all appearance-none cursor-pointer"
                    >
                        <option value="general">📄 General Summary</option>
                        <option value="instruction">🛠 How-To / Instruction</option>
                        <option value="lecture">🎓 Lecture / Detailed Notes</option>
                        <option value="key_points">💡 Key Takeaways</option>
                        <option value="eli5">👶 Explain Like I'm 5</option>
                    </select>
                </div>
                <div class="relative">
                    <select 
                        wire:model="targetLanguage"
                        class="w-full md:w-40 bg-black/40 border border-white/10 rounded-2xl px-4 py-4 text-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500/50 transition-all appearance-none cursor-pointer"
                    >
                        <option value="ru">🇷🇺 RU</option>
                        <option value="en">🇺🇸 EN</option>
                        <option value="sr">🇷🇸 SR</option>
                    </select>
                </div>
                <button type="submit" wire:loading.attr="disabled" class="px-8 bg-gradient-to-r from-orange-600 to-red-600 rounded-2xl font-semibold hover:scale-105 transition-transform disabled:opacity-50">
                    <span wire:loading.remove>Summarize</span>
                    <span wire:loading>Processing...</span>
                </button>
            </div>
            @error('url') <p class="text-red-500 text-sm ml-2">{{ $message }}</p> @enderror
        </form>

        {{-- Progress Bar (Check 2.3: wire:poll fallback) --}}
        @if($currentSummary && in_array($currentSummary->status, ['pending', 'processing']))
            <div class="mt-8 space-y-4" wire:poll.5s="refreshStatus">
                <div class="flex justify-between text-sm items-center">
                    <span class="text-orange-500 font-medium animate-pulse">
                        {{ $currentSummary->status === 'pending' ? 'Waiting in queue...' : 'AI is analyzing video...' }}
                    </span>
                    <div class="flex items-center gap-4">
                        <span class="text-gray-400">Please wait</span>
                        <button wire:click="cancelSummary({{ $currentSummary->id }})" class="text-red-400 hover:text-red-300 text-xs font-medium px-2 py-1 bg-red-500/10 rounded-md transition-colors">
                            Cancel & Refund
                        </button>
                    </div>
                </div>
                <div class="w-full bg-white/10 rounded-full h-2 overflow-hidden">
                    <div class="bg-gradient-to-r from-orange-500 to-red-600 h-full animate-progress-ind"></div>
                </div>
            </div>
        @endif

        {{-- Error State --}}
        @if($currentSummary && $currentSummary->status === 'failed')
            <div class="mt-8 p-4 bg-red-500/10 border border-red-500/20 rounded-2xl flex items-center gap-3 text-red-500">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div class="text-sm font-medium">Processing failed. Your credit has been refunded. Please try a different video or try again later.</div>
            </div>
        @endif
    </div>

    {{-- Results Section --}}
    @if($currentSummary && $currentSummary->status === 'completed')
        <div class="bg-white/5 border border-white/10 p-8 rounded-3xl backdrop-blur-xl animate-fade-in">
            <div class="flex justify-between items-start mb-6">
                <h3 class="text-xl font-bold text-white">Summary Result</h3>
                {{-- Action Bar (Roadmap Day 6) --}}
                <div class="flex items-center gap-2" x-data="{ copied: false, downloading: false }">
                    {{-- Copy to clipboard --}}
                    <button
                        @click="navigator.clipboard.writeText($refs.summaryText.innerText).then(() => { copied = true; setTimeout(() => copied = false, 2000) })"
                        class="p-2 rounded-lg bg-white/5 hover:bg-white/15 transition-colors text-gray-400 hover:text-white"
                        title="Copy to clipboard"
                    >
                        <span x-show="!copied">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                        </span>
                        <span x-show="copied" x-cloak class="text-green-400 text-xs font-medium">Copied!</span>
                    </button>
                    {{-- Download .md --}}
                    <button
                        @click="const blob = new Blob([$refs.summaryText.innerText], {type: 'text/markdown'}); const a = document.createElement('a'); a.href = URL.createObjectURL(blob); a.download = 'summary-{{ $currentSummary->video_id }}.md'; a.click();"
                        class="p-2 rounded-lg bg-white/5 hover:bg-white/15 transition-colors text-gray-400 hover:text-white"
                        title="Download as Markdown"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </button>
                    {{-- Download PDF --}}
                    <a
                        href="{{ route('summary.download', $currentSummary) }}"
                        target="_blank"
                        @click="downloading = true; setTimeout(() => downloading = false, 5000)"
                        class="p-2 rounded-lg bg-white/5 hover:bg-white/15 transition-colors text-gray-400 hover:text-white"
                        :class="downloading && 'pointer-events-none opacity-50'"
                        title="Download PDF"
                    >
                        <svg x-show="!downloading" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        <svg x-show="downloading" x-cloak class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    </a>
                </div>
            </div>
            <div class="prose prose-invert max-w-none text-gray-300" x-ref="summaryText">
                {!! Str::markdown($currentSummary->summary, ['html_input' => 'strip']) !!}
            </div>
            <div class="mt-6 pt-6 border-t border-white/10 flex gap-4 text-xs text-gray-500">
                <span>ID: {{ $currentSummary->video_id }}</span>
                <span>Tokens: {{ $currentSummary->tokens_used }}</span>
            </div>
        </div>
    @endif

    {{-- Recent History --}}
    <div class="space-y-4">
        <h3 class="text-lg font-semibold text-gray-400 ml-2">Recent Summaries</h3>
        @foreach($summaries as $item)
            <div class="bg-white/5 border border-white/10 p-4 rounded-2xl flex items-center justify-between hover:bg-white/10 transition-colors group">
                <div class="flex items-center gap-4">
                    <img src="https://img.youtube.com/vi/{{ $item->video_id }}/default.jpg" class="w-16 h-10 rounded-lg object-cover" alt="Video thumb">
                    <div>
                        <p class="text-white font-medium truncate max-w-xs">{{ $item->video_id }}</p>
                        <p class="text-xs text-gray-500">{{ $item->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs px-2 py-1 rounded-md {{ $item->status === 'completed' ? 'bg-green-500/10 text-green-500' : 'bg-orange-500/10 text-orange-500' }}">
                        {{ ucfirst($item->status) }}
                    </span>
                    <button wire:click="loadSummary({{ $item->id }})" class="p-2 opacity-0 group-hover:opacity-100 transition-opacity" title="View Summary">
                        <svg class="w-5 h-5 text-gray-400 hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                    <button wire:click="deleteSummary({{ $item->id }})" class="p-2 opacity-0 group-hover:opacity-100 transition-opacity" title="Delete Summary" wire:confirm="Are you sure you want to delete this summary?">
                        <svg class="w-5 h-5 text-red-500/70 hover:text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </div>
        @endforeach
    </div>

    <style>
        @keyframes progress-ind {
            0% { width: 0; }
            50% { width: 70%; }
            100% { width: 100%; }
        }
        .animate-progress-ind {
            animation: progress-ind 20s ease-in-out infinite;
        }
        .animate-fade-in {
            animation: fadeIn 0.5s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</div>
