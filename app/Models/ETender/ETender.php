<?php

namespace App\Models\ETender;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ETender extends Model
{
    use HasFactory;

    // السماح بالحفظ الجماعي لجميع الحقول
    protected $guarded = [];

    // تحويل التواريخ تلقائياً
    protected $casts = [
        'date_of_purchase' => 'date',
        'date_of_submission' => 'date',
        'date_of_submission_ba' => 'date',
        'date_of_submission_after_review' => 'date',
        'last_follow_up_date' => 'date',
        'has_third_party' => 'boolean',
    ];

    // العلاقة مع نقاط الاتصال
    public function focalPoints(): HasMany
    {
        return $this->hasMany(FocalPointE::class, 'e_tender_id');
    }
}
