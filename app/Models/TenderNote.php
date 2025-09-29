<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;


class TenderNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'content',
        'edited_by_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];




    /**
     * Get the parent noteable model (tender).
     */
    public function noteable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user that created the note.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function editor(): BelongsTo
    {
        // هذه الدالة تخبر Laravel كيف يجد المستخدم المُعدِّل
        // عبر حقل 'edited_by_id'.
        return $this->belongsTo(User::class, 'edited_by_id');
    }
}
