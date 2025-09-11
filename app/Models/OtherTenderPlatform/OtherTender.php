<?php

namespace App\Models\OtherTenderPlatform;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Models\OtherTenderPlatform\FocalPointO;

class OtherTender extends Model
{
    use HasFactory;

    protected $table = 'other_tenders';

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

    protected $casts = [
        'date_of_purchase' => 'date',
        'date_of_submission' => 'date',
        'date_of_submission_ba' => 'date',
        'date_of_submission_after_review' => 'date',
        'last_follow_up_date' => 'date',
        'has_third_party' => 'boolean',
    ];

    // protected function quarter(): Attribute
    // {
    //     return Attribute::make(
    //         get: fn () => 'Q' . ceil($this->date_of_submission->month / 3),
    //     );
    // }

    protected function quarter(): Attribute
    {
        return Attribute::make(
            get: function () {

                if (!$this->date_of_submission) {
                    return null;
                }
                $date = \Carbon\Carbon::parse($this->date_of_submission);

                return "Q" . $date->quarter . ", " . $date->year;
            }
        );
    }

    /**
     * Get the focal points for the tender.
     */
    public function focalPoints(): HasMany
    {
        return $this->hasMany(FocalPointO::class, 'other_tender_id');
    }
}
