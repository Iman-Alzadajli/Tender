<?php

namespace App\Models\InternalTender;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FocalPoint extends Model 
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'internal_tender_id',
        'name',
        'phone',
        'email',
        'department',
        'other_info',
    ];

  
    public function internalTender(): BelongsTo
    {
        return $this->belongsTo(InternalTender::class);
    }
}
