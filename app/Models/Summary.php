<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Summary extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'video_id',
        'status',
        'transcript',
        'summary',
        'tokens_used',
        'locked_until',
        'prompt_mode',
        'language',
    ];

    public function chunks()
    {
        return $this->hasMany(SummaryChunk::class);
    }
}
