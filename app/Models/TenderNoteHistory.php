<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenderNoteHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'tender_note_id',
        'user_id',
        'old_content',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tenderNote(): BelongsTo
    {
        return $this->belongsTo(TenderNote::class);
    }
}
