<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Summary;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Gate;

class SummaryController extends Controller
{
    public function download(Summary $summary)
    {
        // Security check
        if ($summary->user_id !== auth()->id()) {
            abort(403);
        }

        $pdf = Pdf::loadView('pdf.summary', [
            'summary' => $summary,
            'userEmail' => auth()->user()->email,
        ]);

        return $pdf->download("summary-{$summary->video_id}.pdf");
    }
}
