<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseType extends Model
{   
    use SoftDeletes;

    protected $table = "purchase_types";

    public $fillable = [
        "code",
        "description"
    ];

    public $timestamps = true;

    protected $casts = ["deleted_at" => 'datetime'];

    public function resources()
    {
      return $this->hasMany(Resource::class);
    }
    
    public function logs()
    {
        return $this->morphMany(TransactionLog::class, 'referrence');
    }
}
