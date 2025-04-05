<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class FunctionObjective extends Pivot
{
    protected $table = 'function_objectives';

    public $fillable = [
        'type_of_function _id',
        'objective_id'
    ];

    public $timestamps = true;

    public function typeOfFunction()
    {
        return $this->belongsTo(TypeOfFunction::class);
    }

    public function objective()
    {
        return $this->belongsTo(Objective::class);
    }
}
