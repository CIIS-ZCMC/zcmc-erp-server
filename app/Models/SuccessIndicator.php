<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuccessIndicator extends Model
{
    protected $table = "success_indicators";

    public $fillable = [
        'code',
        'description'
    ];

    public $timestamps = true;

    protected $casts = ['deleted_at' => 'datetime'];

    // Uncomment once the Objective Success Indicators Models exist
<<<<<<< HEAD
    // public function objectiveSuccessIndicators()
    // {
    //     return $this->hasMany(ObjectiveSuccessIndicator::class);
    // }

=======
    public function objectiveSuccessIndicators()
    {
        return $this->hasMany(ObjectiveSuccessIndicator::class);
    }
    
>>>>>>> 9202c67c6f8317e9af0cfd626b2ac71eec2096fb
    public function logs()
    {
        return $this->morphMany(TransactionLog::class, 'referrence');
    }
}
