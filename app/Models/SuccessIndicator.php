<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SuccessIndicator extends Model
{
    use SoftDeletes;
    protected $table = "success_indicators";

    public $fillable = [
        'objective_id',
        'code',
        'description',
    ];

    public $timestamps = true;

    protected $casts = ['deleted_at' => 'datetime'];

    public function applicationObjectives(): HasMany
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
