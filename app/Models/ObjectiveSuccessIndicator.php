<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ObjectiveSuccessIndicator extends Model
{
    protected $table = "objective_success_indicators";

    public $fillable = [
        "objective_id",
        "success_indicator_id"
    ];

    public $timestamps = true;

    public function objective():BelongsTo
    {
        return $this->belongsTo(Objective::class);
    }

    public function successIndicator():BelongsTo
    { 
        return $this->belongsTo(SuccessIndicator::class);
    }
    
    public function otherSuccessIndicator()
    {
        return $this->hasOne(OtherSuccessIndicator::class, 'application_objective_id');
    }
}
