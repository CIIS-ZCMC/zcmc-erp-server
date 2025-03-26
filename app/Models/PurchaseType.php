<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseType extends Model
{   
    protected $table = "purchase_types";

    public $fillable = [
        "code",
        "description",
        "deleted_at"
    ];

    public $timestamps = true;

    // Uncomment once the Resource
    // public function resources()
    // {
    //   return $this->hasMany(Resource::class);
    // }
    
    public function logs()
    {
        return $this->morphMany(TransactionLog::class, 'referrence');
    }
}
