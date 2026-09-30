<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\AppLog;
use Livewire\WithPagination;

class SystemLogs extends Component
{
    use WithPagination;

    public function mount()
    {
        // Temporarily allow access for all logged in users to debug
    }

    public function render()
    {
        // Admin sees EVERYTHING
        $logs = AppLog::orderBy('created_at', 'desc')
            ->paginate(50);

        return view('livewire.system-logs', [
            'logs' => $logs
        ])->layout('layouts.app');
    }
}
