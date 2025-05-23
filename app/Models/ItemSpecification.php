<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class ItemSpecification extends Model
{
    use SoftDeletes, Searchable;
    protected $table = 'item_specifications';

    public $fillable = [
        'description',
        'item_id',
        'item_request_id',
    ];

    public $timestamps = true;

    protected $casts = ['deleted_at' => 'datetime'];

    public function toSearchableArray()
    {
        return [
            'description' => $this->description,
        ];
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function itemRequest()
    {
        return $this->belongsTo(ItemRequest::class);
    }

    public function itemSpecifications()
    {
        return $this->hasMany(ItemSpecification::class);
    }
}
