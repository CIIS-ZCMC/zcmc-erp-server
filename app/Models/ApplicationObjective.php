<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ApplicationObjective extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'aop_application_id',
        'objective_id',
        'success_indicator_id'
    ];

    public function aopApplication(): BelongsTo
    {
        return $this->belongsTo(AopApplication::class);
    }

    public function objective(): BelongsTo
    {
        return $this->belongsTo(Objective::class, 'objective_id', 'id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    public function otherObjective(): HasOne
    {
        return $this->hasOne(OtherObjective::class, 'application_objective_id', 'id');
    }

    public function otherSuccessIndicator(): HasOne
    {
        return $this->hasOne(OtherSuccessIndicator::class, 'application_objective_id', 'id');
    }

    public function successIndicator()
    {
        return $this->belongsTo(SuccessIndicator::class, 'success_indicator_id', 'id');
    }

    protected static function booted()
    {
        static::deleting(function ($objective) {
            if (!$objective->isForceDeleting()) {
                foreach ($objective->activities as $activity) {
                    $activity->delete();
                }
            }
        });
    }
}
