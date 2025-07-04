<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Item extends Model
{
    use SoftDeletes, Searchable;

    protected $table = 'items';

    public $fillable = [
        "item_unit_id",
        "item_category_id",
        "item_classification_id",
        "terminologies_category_id",
        "name",
        "code",
        "image",
        "estimated_budget"
    ];

    public $timestamps = true;

    protected $casts = ['deleted_at' => 'datetime'];

    public function toSearchableArray()
    {
        return [
            'name' => $this->name,
            'code' => $this->code
        ];
    }

    public function terminologyCategory()
    {
        return $this->belongsTo(TerminologyCategory::class, 'terminologies_category_id');
    }

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

    public function itemSpecifications()
    {
        return $this->hasMany(ItemSpecification::class);
    }

    public function ppmpItems()
    {
        return $this->hasMany(PpmpItem::class);
    }

    public function logs()
    {
        return $this->morphMany(TransactionLog::class, 'referrence');
    }
}
