<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemRequest extends Model
{
    use SoftDeletes;

    protected $table = 'item_requests';

    public $fillable = [
        "name",
        "code",
        "image",
        "variant_id",
        "estimated_budget",
        "item_unit_id",
        "item_category_id",
        "item_classification_id",
        "terminologies_category_id",
        "status",
        "reason",
        "requested_by",
        "action_by"
    ];

    public $timestamps = true;

    protected $casts = ['deleted_at' => 'datetime'];

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

    public function terminologyCategory()
    {
        return $this->belongsTo(TerminologyCategory::class, "terminologies_category_id", "id");
    }


    public function itemSpecifications()
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
