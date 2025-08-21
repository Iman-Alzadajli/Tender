<?php

namespace App\Models\OtherTenderPlatform;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FocalPointO extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'focal_points_o'; // افتراضيًا، يمكنك تغييره

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'other_tender_id', // تم التغيير ليعكس العلاقة الجديدة
        'name',
        'phone',
        'email',
        'department',
        'other_info',
    ];

    /**
     * Get the tender that the focal point belongs to.
     */
    public function otherTender(): BelongsTo
    {
        // العلاقة الآن مع موديل OtherTender
        return $this->belongsTo(OtherTender::class, 'other_tender_id');
    }
}
