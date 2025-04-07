<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OthersObjective extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_objective_id',
        'description'
    ];

    public function applicationObjective()
    {
        return $this->belongsTo(ApplicationObjective::class);
    }
}
