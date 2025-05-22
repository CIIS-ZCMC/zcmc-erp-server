<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Snomed extends Model
{
    use SoftDeletes;

    protected $table = 'snomeds';

    public $fillable = [
        'code'
    ];

    public $timestamps = TRUE;

    protected $casts = [
        'deleted_at' => 'datetime'
    ];

    public function items()
    {
        return $this->hasMany(Item::class);
    }
}
