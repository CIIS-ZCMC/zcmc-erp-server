<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FileRecord extends Model
{
    protected $tables = 'file_records';

    public $fillable = [
        'item_id',
        'item_request_id',
        'path',
        'name',
        'size',
        'type'
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
}
