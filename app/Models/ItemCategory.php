<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class ItemCategory extends Model
{
    use SoftDeletes, Searchable;

    protected $table = "item_categories";

    public $fillable = [
        "name",
        "code",
        "description",
        "item_category_id"
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

    public function terminologyCategories()
    {
        return $this->hasMany(TerminologyCategory::class);
    }
}
