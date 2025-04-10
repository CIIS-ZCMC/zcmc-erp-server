<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use SoftDeletes;
    
    protected $table = 'items';

    public $fillable = [
        "item_unit_id",
        "item_category_id",
        "item_classification_id",
        "name",
        "code",
        "image",
        "variant",
        "estimated_budget"
    ];

    protected $casts = ['deleted_at' => 'datetime'];

    public $timestamps = true;

    public function itemUnit()
    {
        return $this->belongsTo(ItemUnit::class);
    }

    public function itemCategory()
    {
        return $this->belongsTo(ItemCategory::class);
    }

    public function itemClassification()
    {
        return $this->belongsTo(ItemClassification::class);
    }

    public function itemSpecification()
    {
        return $this->hasMany(ItemSpecification::class);
    }

    public function logs()
    {
        return $this->morphMany(TransactionLog::class, 'referrence');
    }
}
