<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemUnit extends Model
{
    use SoftDeletes;

    protected $table = 'item_units';

    public $fillable = [
        "name",
        "code",
        "description",
        "deleted_at"
    ];

    public $timestamps = true;

    protected $casts = ['deleted_at' => 'datetime'];

    public function items()
    {
        return $this->hasMany(Item::class);
    }
    
    public function logs()
    {
        return $this->morphMany(TransactionLog::class, 'referrence');
    }
}
