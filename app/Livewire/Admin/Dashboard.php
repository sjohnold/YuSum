<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\User;
use App\Models\Summary;
use App\Models\AppLog;

class Dashboard extends Component
{
    public function render()
    {
        $stats = [
            'total_users' => User::count(),
            'total_summaries' => Summary::where('status', 'completed')->count(),
            'failed_summaries' => Summary::where('status', 'failed')->count(),
            'total_tokens' => Summary::sum('tokens_used'),
            'recent_errors' => AppLog::where('level', 'error')->count(),
        ];

        return view('livewire.admin.dashboard', compact('stats'))->layout('layouts.app');
    }
}
