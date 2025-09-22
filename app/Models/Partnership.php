<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Partnership extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name',
        'person_name',
        'phone',
        'email',
        'details',
    ];

    public function partnerable(): MorphTo
    {
        return $this->morphTo();
    }
}
