<?php

namespace App\Models\ETender;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FocalPointE extends Model
{
    use HasFactory;

    // protected $guarded = [];
    protected $fillable = [
        'name',
        'phone',
        'email',
        'department',
        'other_info',
    ];

    public function tender(): BelongsTo
    {
        return $this->belongsTo(ETender::class, 'e_tender_id');
    }
}
