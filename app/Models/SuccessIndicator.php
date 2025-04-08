<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuccessIndicator extends Model
{
    protected $table = "success_indicators";

    public $fillable = [
        'code',
        'description',
        'deleted_at'
    ];

    public $timestamps = true;

    // Uncomment once the Objective Success Indicators Models exist
    // public function objectiveSuccessIndicators()
    // {
    //     return $this->hasMany(ObjectiveSuccessIndicator::class);
    // }

    public function logs()
    {
        return $this->morphMany(TransactionLog::class, 'referrence');
    }
}
