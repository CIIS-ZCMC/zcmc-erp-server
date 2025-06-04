<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemReferenceTerminology extends Model
{
    use SoftDeletes;

    protected $table = 'reference_terminologies';

    public $fillable = [
        'code',
        'system',
        'description',
        'category_id'
    ];

    public $timestamps = TRUE;

    protected $casts = [
        'deleted_at' => 'datetime'
    ];

    public function items()
    {
        return $this->hasMany(Item::class);
    }

    public function itemRequests()
    {
        return $this->hasMany(ItemRequest::class);
    }

    public function terminologyCategories()
    {
        return $this->hasMany(TerminologyCategory::class);
    }
}
