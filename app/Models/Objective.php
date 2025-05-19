<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Objective extends Model
{
    use SoftDeletes;

    protected $table = 'objectives';

    protected $fillable = [
        'type_of_function_id',
        'code',
        'description',
    ];

    public $timestamps = true;

    protected $casts = ['deleted_at' => 'datetime'];

    public function applicationObjectives()
    {
        return $this->hasMany(ApplicationObjective::class);
    }

    public function successIndicators()
    {
        return $this->hasMany(SuccessIndicator::class);
    }

    public function typeOfFunction()
    {
        return $this->belongsTo(TypeOfFunction::class);
    }

    public function logs()
    {
        return $this->morphMany(TransactionLog::class, 'referrence');
    }
}
