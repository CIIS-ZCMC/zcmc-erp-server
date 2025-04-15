<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TypeOfFunction extends Model
{
    protected $table = "type_of_functions";

    public $fillable = ['code','type'];

    public $timestamps = true;
    
    protected $casts = ['deleted_at' => 'datetime'];
    
    public function objective() {
        return $this->hasMany(Objective::class);
    }

    public function objectives()
    {
        return $this->hasMany(Objective::class);
    }
    
    public function logs()
    {
        return $this->morphMany(TransactionLog::class, 'referrence');
    }
}
