<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseType extends Model
{   
    protected $table = "purchase_types";

    public $fillable = [
        "description",
        "code"
    ];

    public $timestamps = true;

    // Uncomment once the Resource
    // public function resources()
    // {
    //   return $this->hasMany(Resource::class);
    // }
}
