<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationTimeline extends Model
{
    protected $fillable = [
        'aop_application_id',
        'ppmp_application_id',
        'user_id',
        'approver_user_id',
        'current_area_id',
        'next_area_id',
        'status',
        'remarks',
        'date_approved',
        'date_returned',
    ];

    protected $dates = [
        'date_approved',
        'date_returned',
    ];

    use SoftDeletes;

    public function aopApplication(): BelongsTo
    {
        return $this->belongsTo(AopApplication::class);
    }

    public function ppmpApplication(): BelongsTo
    {
        return $this->belongsTo(PpmpApplication::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function currentArea(): BelongsTo
    {
        return $this->belongsTo(AssignedArea::class, 'current_area_id');
    }

    public function nextArea(): BelongsTo
    {
        return $this->belongsTo(AssignedArea::class, 'next_area_id');
    }
}
