<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApplicationObjective extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'aop_application_id',
        'function_objective_id',
        'objective_code',
        'success_indicator_id'

    ];

    public function aopApplication(): BelongsTo
    {   
        return $this->belongsTo(AopApplication::class);
    }

    public function objective()
    {
        return $this->belongsTo(Objective::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    public function othersObjective()
    {
        return $this->hasOne(OthersObjective::class, 'application_objective_id');
    }

    public function otherSuccessIndicator()
    {
        return $this->hasOne(OtherSuccessIndicator::class);
    }

    public function successIndicator()
    {
        return $this->belongsTo(SuccessIndicator::class, 'success_indicator_id');
    }
}
