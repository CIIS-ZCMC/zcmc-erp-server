<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Objective extends Model
{
    protected $table = 'objectives';

    protected $fillable = [
        'uuid',
        'description',
        'code'
    ];

    public $timestamps = true;

    // Uncomment this if FunctionObjective Exist.
    // public function functionObjectives()
    // {
    //     return $this->hasMany(FunctionObjective::class);
    // }
}
