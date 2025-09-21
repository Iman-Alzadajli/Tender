<?php

namespace App\Models\ETender;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Models\TenderNote;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ETender extends Model
{
    use HasFactory;

    // // السماح بالحفظ الجماعي لجميع الحقول
    // protected $guarded = [];
    protected $table = 'e_tenders';

    protected $fillable = [
        'name',
        'number',
        'client_type',
        'client_name',
        'assigned_to',
        'date_of_purchase',
        'date_of_submission',
        'reviewed_by',
        'last_date_of_clarification',
        'submission_by',
        'date_of_submission_after_review',
        'has_third_party',
        'partnership_company',
        'partnership_person',
        'partnership_phone',
        'partnership_email',
        'partnership_details',
        'last_follow_up_date',
        'follow_up_channel',
        'follow_up_notes',
        'status',
        'reason_of_cancel',
        'submitted_price',
        'awarded_price',
        'reason_of_recall',


    ];

    // تحويل تواريخ طؤيقة كتابة 
    protected $casts = [
        'date_of_purchase' => 'date',
        'date_of_submission' => 'date',
        'last_date_of_clarification' => 'date',
        'date_of_submission_after_review' => 'date',
        'last_follow_up_date' => 'date',
        'has_third_party' => 'boolean',
    ];

    // protected function quarter(): Attribute
    // {
    //     return Attribute::make(
    //         get: fn() => 'Q' . ceil($this->date_of_submission->month / 3),
    //     );
    // }

    protected function quarter(): Attribute
    {
        return Attribute::make(
            get: function () {
                //  التأكد من وجود تاريخ قبل محاولة استخدامه
                if (!$this->date_of_submission) {
                    return null;
                }
                $date = \Carbon\Carbon::parse($this->date_of_submission);
                //  هذا هو التعديل: نولد مفتاحًا مثل "Q1, 2025" 
                return "Q" . $date->quarter . ", " . $date->year;
            }
        );
    }


    //  علاقات 
    public function focalPoints(): HasMany
    {
        return $this->hasMany(FocalPointE::class, 'e_tender_id');
    }

    public function notes(): MorphMany
    {
        return $this->morphMany(TenderNote::class, 'noteable');
    }
}
