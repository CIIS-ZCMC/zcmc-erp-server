<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Target extends Model
{
    use HasFactory, SoftDeletes;


    use HasFactory;

    protected $fillable = [
        'activity_id',
        'first_quarter',
        'second_quarter',
        'third_quarter',
        'fourth_quarter',
    ];

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }
}
