<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SummaryChunk extends Model
{
    use HasFactory;

    protected $fillable = [
        'summary_id',
        'index',
        'content',
        'summary',
        'tokens_used',
    ];

    public function summary()
    {
        return $this->belongsTo(Summary::class);
    }
}
