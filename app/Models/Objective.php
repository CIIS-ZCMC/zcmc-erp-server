<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Objective extends Model
{
    protected $table = 'objectives';

    protected $fillable = [
        'code',
        'description'
    ];

    public $timestamps = true;

    protected $casts = ['deleted_at' => 'datetime'];

    public function functionObjectives()
    {
        return $this->hasMany(FunctionObjective::class);
    }

    public function logs()
    {
        return $this->morphMany(TransactionLog::class, 'referrence');
    }

    
}
