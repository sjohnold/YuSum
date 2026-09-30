<div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8 animate-fade-in">
    <div class="flex items-center justify-between mb-10">
        <div>
            <h1 class="text-4xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-purple-400 to-orange-400 tracking-tight">Admin Overview</h1>
            <p class="text-gray-400 mt-2">Monitor your SaaS metrics and system health in real-time.</p>
        </div>
        <div class="flex gap-4">
            <a href="{{ route('admin.users') }}" class="group relative px-6 py-3 bg-white/5 border border-white/10 hover:border-purple-500/50 hover:bg-purple-500/10 text-white rounded-2xl font-medium transition-all shadow-lg overflow-hidden flex items-center gap-2">
                <div class="absolute inset-0 bg-gradient-to-r from-purple-500/20 to-transparent translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-700"></div>
                <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                Manage Users
            </a>
            <a href="{{ route('admin.logs') }}" class="group relative px-6 py-3 bg-white/5 border border-white/10 hover:border-red-500/50 hover:bg-red-500/10 text-white rounded-2xl font-medium transition-all shadow-lg overflow-hidden flex items-center gap-2">
                <div class="absolute inset-0 bg-gradient-to-r from-red-500/20 to-transparent translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-700"></div>
                <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                System Logs
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        {{-- Total Users Card --}}
        <div class="relative bg-white/5 p-8 rounded-3xl border border-white/10 backdrop-blur-xl hover:-translate-y-1 hover:shadow-[0_0_30px_rgba(168,85,247,0.15)] transition-all overflow-hidden group">
            <div class="absolute top-0 right-0 p-6 opacity-20 group-hover:scale-110 group-hover:text-purple-400 transition-all">
                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <h3 class="text-sm font-semibold text-purple-400 uppercase tracking-wider">Total Users</h3>
            <p class="text-5xl font-black text-white mt-4 tracking-tighter">{{ $stats['total_users'] }}</p>
        </div>

        {{-- Completed Summaries Card --}}
        <div class="relative bg-white/5 p-8 rounded-3xl border border-white/10 backdrop-blur-xl hover:-translate-y-1 hover:shadow-[0_0_30px_rgba(34,197,94,0.15)] transition-all overflow-hidden group">
            <div class="absolute top-0 right-0 p-6 opacity-20 group-hover:scale-110 group-hover:text-green-400 transition-all">
                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <h3 class="text-sm font-semibold text-green-400 uppercase tracking-wider">Completed Jobs</h3>
            <p class="text-5xl font-black text-white mt-4 tracking-tighter">{{ number_format($stats['total_summaries']) }}</p>
        </div>

        {{-- Total Tokens Card --}}
        <div class="relative bg-white/5 p-8 rounded-3xl border border-white/10 backdrop-blur-xl hover:-translate-y-1 hover:shadow-[0_0_30px_rgba(249,115,22,0.15)] transition-all overflow-hidden group">
            <div class="absolute top-0 right-0 p-6 opacity-20 group-hover:scale-110 group-hover:text-orange-400 transition-all">
                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            </div>
            <h3 class="text-sm font-semibold text-orange-400 uppercase tracking-wider">Total AI Tokens</h3>
            <p class="text-5xl font-black text-white mt-4 tracking-tighter">{{ number_format($stats['total_tokens']) }}</p>
        </div>

        {{-- Failed Summaries Card --}}
        <div class="relative bg-white/5 p-8 rounded-3xl border border-white/10 backdrop-blur-xl hover:-translate-y-1 hover:shadow-[0_0_30px_rgba(239,68,68,0.15)] transition-all overflow-hidden group">
            <div class="absolute top-0 right-0 p-6 opacity-20 group-hover:scale-110 group-hover:text-red-500 transition-all">
                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <h3 class="text-sm font-semibold text-red-400 uppercase tracking-wider">Failed Jobs</h3>
            <p class="text-5xl font-black text-white mt-4 tracking-tighter">{{ $stats['failed_summaries'] }}</p>
        </div>

        {{-- Recent Errors Card --}}
        <div class="relative bg-white/5 p-8 rounded-3xl border border-white/10 backdrop-blur-xl hover:-translate-y-1 hover:shadow-[0_0_30px_rgba(239,68,68,0.15)] transition-all overflow-hidden group md:col-span-2 lg:col-span-2 bg-gradient-to-br from-red-500/5 to-transparent">
            <div class="absolute top-0 right-0 p-6 opacity-20 group-hover:scale-110 group-hover:text-red-500 transition-all">
                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            <h3 class="text-sm font-semibold text-red-500 uppercase tracking-wider">Critical System Errors</h3>
            <p class="text-5xl font-black text-white mt-4 tracking-tighter">{{ $stats['recent_errors'] }}</p>
            <p class="mt-4 text-sm text-gray-400">If this number is growing rapidly, check the System Logs immediately to identify pipeline failures.</p>
        </div>
    </div>
</div>
<style>
    .animate-fade-in { animation: fadeIn 0.6s ease-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
</style>
