<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SuccessIndicator extends Model
{
    protected $table = "success_indicators";

    public $fillable = [
        'objective_id',
        'code',
        'description',
        'objective_id'
    ];

    public $timestamps = true;

    protected $casts = ['deleted_at' => 'datetime'];

    public function applicationObjectives():HasMany
    {
        return $this->hasMany(ApplicationObjective::class);
    }

    public function objective()
    {
        return $this->belongsTo(Objective::class);
    }
    
    public function logs()
    {
        return $this->morphMany(TransactionLog::class, 'referrence');
    }
}
