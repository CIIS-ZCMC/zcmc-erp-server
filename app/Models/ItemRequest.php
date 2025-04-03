<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemRequest extends Model
{
    protected $table = 'item_requests';

    public $fillable = [
        "item_unit_id",
        "item_category_id",
        "item_classification_id",
        "name",
        "code",
        "image",
        "variant",
        "estimated_budget",
        "status",
        "reason",
        "deleted_at",
        "requested_by",
        "action_by"
    ];

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

    public function requestedBy()
    {
        return $this->belongsTo(User::class);
    }

    public function actionBy()
    {
        return $this->belongsTo(User::class);
    }
}
