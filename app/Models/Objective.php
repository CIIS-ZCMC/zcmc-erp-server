<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Objective extends Model
{
    protected $table = 'objectives';

    protected $fillable = [
        'code',
        'description',
        'deleted_at'
    ];

    public $timestamps = true;

    // Uncomment this if FunctionObjective Exist.
    public function functionObjectives()
    {
        return $this->hasMany(FunctionObjective::class);
    }

    public function objectiveSuccessIndicators()
    {
        return $this->hasMany(ObjectiveSuccessIndicator::class);
    }
    
    public function otherObjective()
    {
        return $this->hasOne(OtherObjective::class, 'application_objective_id');
    }

    public function logs()
    {
        return $this->morphMany(TransactionLog::class, 'referrence');
    }
}
