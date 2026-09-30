<div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-white">Manage Users</h1>
        <a href="{{ route('admin.dashboard') }}" class="text-orange-500 hover:text-orange-400">&larr; Back to Dashboard</a>
    </div>

    @if (session()->has('message'))
        <div class="mb-4 p-4 bg-green-500/20 text-green-400 rounded-xl border border-green-500/30">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 p-4 bg-red-500/20 text-red-400 rounded-xl border border-red-500/30">
            {{ session('error') }}
        </div>
    @endif

    <div class="mb-6">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search by name or email..." class="w-full md:w-1/3 bg-white/5 border border-white/10 rounded-xl px-4 py-2 text-white focus:outline-none focus:border-orange-500">
    </div>

    <div class="bg-white/5 border border-white/10 rounded-2xl overflow-hidden">
        <table class="min-w-full divide-y divide-white/10">
            <thead class="bg-white/5">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Credits</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Role</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/10 bg-transparent">
                @foreach($users as $user)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-white">{{ $user->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-white font-bold">{{ $user->credits }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($user->is_admin)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">Admin</span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">User</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium flex justify-end gap-2">
                            <button wire:click="addCredits({{ $user->id }}, 10)" class="text-orange-500 hover:text-orange-400 bg-orange-500/10 px-3 py-1 rounded">+10 Credits</button>
                            <button wire:click="addCredits({{ $user->id }}, 100)" class="text-green-500 hover:text-green-400 bg-green-500/10 px-3 py-1 rounded">+100</button>
                            <button wire:click="toggleAdmin({{ $user->id }})" class="text-purple-500 hover:text-purple-400 bg-purple-500/10 px-3 py-1 rounded" wire:confirm="Are you sure you want to change admin status?">Toggle Admin</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="p-4 border-t border-white/10">
            {{ $users->links() }}
        </div>
    </div>
</div>
