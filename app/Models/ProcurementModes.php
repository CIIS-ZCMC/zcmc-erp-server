<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcurementModes extends Model
{
    protected $table = 'procurement_modes';

    public $fillable = [
        'name',
        'deleted_at'
    ];

    public $timestamps = true;

    // uncomment when PPMPT Items exist
    // public function ppmpItems()
    // {
    //     return $this->hasMany(PPMPItem::class);
    // }
}
