<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class ItemClassification extends Model
{
    use SoftDeletes, Searchable;
    
    protected $table = "item_classifications";

    public $fillable = [
        "name",
        "code",
        "description"
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
