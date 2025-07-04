<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtherSuccessIndicator extends Model
{
    protected $fillable = [
        'application_objective_id',
        'description'
    ];

    public $timestamps = true;

    public function applicationObjective()
    {
        return $this->belongsTo(ApplicationObjective::class);
    }

}
