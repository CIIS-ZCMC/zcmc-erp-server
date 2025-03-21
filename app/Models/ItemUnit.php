<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemUnit extends Model
{
    protected $table = 'item_units';

    public $fillable = [
        "name",
        "code",
        "description"
    ];

    public $timestamps = true;

    public function items()
    {
        return $this->hasMany(Item::class);
    }
    
    /**
     * Get all logs that reference this item unit.
     */
    public function logs()
    {
        return $this->morphMany(TransactionLog::class, 'referrence');
    }
}
