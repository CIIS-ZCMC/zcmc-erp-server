<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemClassification extends Model
{
    protected $table = "item_classifications";

    public $fillable = [
        "item_category_id",
        "name",
        "code",
        "description",
        "deleted_at"
    ];

    public $timestamps = true;

    public function items()
    {
        return $this->hasMany(Item::class);
    }

    public function itemCategory()
    {
        return $this->belongsTo(ItemCategory::class);
    }
    
    public function logs()
    {
        return $this->morphMany(TransactionLog::class, 'referrence');
    }
}
