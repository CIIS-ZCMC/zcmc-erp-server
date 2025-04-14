<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class ItemUnit extends Model
{
    use SoftDeletes, Searchable;

    protected $table = 'item_units';

    public $fillable = [
        "name",
        "code",
        "description",
        "deleted_at"
    ];

    public $timestamps = true;

    protected $casts = ['deleted_at' => 'datetime'];
    
    public function toSearchableArray()
    {
        return [
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
        ];
    }

    public function items()
    {
        return $this->hasMany(Item::class);
    }
    
    public function logs()
    {
        return $this->morphMany(TransactionLog::class, 'referrence');
    }
}
