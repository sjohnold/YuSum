<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\User;
use Livewire\WithPagination;

class UsersManager extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function addCredits(int $userId, int $amount): void
    {
        if ($amount < 1 || $amount > 10000) {
            $this->addError('credits', 'Credits must be between 1 and 10,000.');
            return;
        }

        $user = User::findOrFail($userId);
        $user->increment('credits', $amount);
        
        session()->flash('message', "Added {$amount} credits to {$user->email}.");
    }

    public function toggleAdmin(int $userId): void
    {
        $user = User::findOrFail($userId);
        // Protect against self-demotion
        if ($user->id === auth()->id()) {
            session()->flash('error', "You cannot remove your own admin status.");
            return;
        }

        $user->is_admin = !$user->is_admin;
        $user->save();
        session()->flash('message', "Admin status updated for {$user->email}.");
    }

    public function render()
    {
        $users = User::where('email', 'like', "%{$this->search}%")
            ->orWhere('name', 'like', "%{$this->search}%")
            ->orderBy('id', 'desc')
            ->paginate(20);

        return view('livewire.admin.users-manager', compact('users'))->layout('layouts.app');
    }
}
