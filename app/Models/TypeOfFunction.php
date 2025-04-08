<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TypeOfFunction extends Model
{
    protected $table = "type_of_functions";

    public $fillable = ['type'];

    public $timestamps = true;
    
    protected $casts = ['deleted_at' => 'datetime'];
    
    //Uncomment once the Function Object Model already exist
    public function functionObjectives()
    {
        return $this->hasMany(FunctionObjective::class);
    }
    
    public function logs()
    {
        return $this->morphMany(TransactionLog::class, 'referrence');
    }
}
