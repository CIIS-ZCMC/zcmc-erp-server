<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Variant extends Model
{
    protected $table = 'variants';

    public $fillable = [
        'name',
        'code'
    ];

    public $timestamps = TRUE;

    public function items()
    {
        return $this->hasMany(Item::class);
    }
}
