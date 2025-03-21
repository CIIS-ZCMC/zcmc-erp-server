<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemClassification extends Model
{
    protected $table = "item_classifications";

    public $fillable = [
        "item_categories_id",
        "name",
        "code",
        "description"
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
}
