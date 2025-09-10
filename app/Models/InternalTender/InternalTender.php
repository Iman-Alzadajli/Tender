<?php

namespace App\Models\InternalTender;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute; 

class InternalTender extends Model
{
    use HasFactory;

    protected $table = 'internal_tenders';


    protected $fillable = [
        'name',
        'number',
        'client_type', 
        'client_name',
        'assigned_to',
        'date_of_purchase',
        'date_of_submission',
        'reviewed_by',
        'date_of_submission_ba',
        'date_of_submission_after_review',
        'has_third_party',
        'last_follow_up_date',
        'follow_up_channel',
        'follow_up_notes',
        'status',
        'reason_of_cancel',
       
    ];

    //لتحديد انواع الداتا لهذه الأعمدة 

    protected $casts = [
        'date_of_purchase' => 'date',
        'date_of_submission' => 'date',
        'date_of_submission_ba' => 'date',
        'date_of_submission_after_review' => 'date',
        'last_follow_up_date' => 'date',
        'has_third_party' => 'boolean',
    ];

    /**
     * هذ لحساب الربع السنوي تلقائيًا
     * عمود افتراضي 
     */
    protected function quarter(): Attribute
    {
        return Attribute::make(
            get: fn () => 'Q' . ceil($this->date_of_submission->month / 3),
        );
    }
 
    //علاقة 

    public function focalPoints(): HasMany
    {
        return $this->hasMany(FocalPoint::class);
    }
}
