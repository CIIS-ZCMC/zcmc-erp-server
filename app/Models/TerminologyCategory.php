<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TerminologyCategory extends Model
{
    use SoftDeletes;

    protected $table = 'terminologies_categories';

    public $fillable = [
        'id',
        'name',
        'category_id',
        'reference_terminology_id'
    ];

    public $timestamps = TRUE;

    protected $casts = [
        'deleted_at' => 'datetime'
    ];

    public function category()
    {
        return $this->belongsTo(ItemCategory::class);
    }

    public function terminology()
    {
        return $this->belongsTo(ItemReferenceTerminology::class, 'reference_terminology_id');
    }

    public function items()
    {
        return $this->hasMany(Item::class);
    }

    public function itemRequests()
    {
        return $this->hasMany(ItemRequest::class);
    }
}
