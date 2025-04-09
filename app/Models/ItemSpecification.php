<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemSpecification extends Model
{
    protected $table = 'item_specifications';

    public $fillable = [
        'item_id',
        'item_request_id',
        'month',
        'year',
        'quantity',
        'item_specification_id'
    ];

    public $timestamps = true;

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function itemRequest()
    {
        return $this->belongsTo(ItemRequest::class);
    }

    public function itemSpecification()
    {
        return $this->belongsTo(ItemSpecification::class);
    }

    public function itemSpecifications()
    {
        return $this->hasMany(ItemSpecification::class);
    }
}
