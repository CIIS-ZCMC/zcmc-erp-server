<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemCategory extends Model
{
    protected $table = "item_categories";

    public $fillable = [
        "name",
        "code",
        "description",
        "deleted_at",
        "item_category_id"
    ];

    public $timestamps = true;

    public function items()
    {
        return $this->hasMany(Item::class);
    }

    public function itemClassifications()
    {
        return $this->hasMany(ItemClassification::class);
    }

    public function logs()
    {
        return $this->morphMany(TransactionLog::class, 'referrence');
    }

    public function itemCategory()
    {
        return $this->belongsTo(ItemCategory::class);
    }

    public function itemCategories()
    {
        return $this->hasMany(ItemCategory::class);
    }
}
