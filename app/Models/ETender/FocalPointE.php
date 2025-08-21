<?php

namespace App\Models\ETender;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FocalPointE extends Model
{
    use HasFactory;

    protected $guarded = [];

    // العلاقة العكسية مع المناقصة
    public function eTender(): BelongsTo
    {
        return $this->belongsTo(ETender::class, 'e_tender_id');
    }
}
