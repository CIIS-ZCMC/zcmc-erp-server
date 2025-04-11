<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PpmpItemComment extends Model
{
    protected $table = 'ppmp_item_comments';

    protected $fillable = [
        'user_id',
        'ppmp_item',
        'comment',
    ];

    public $timestamps = true;

    public function ppmpItem()
    {
        return $this->belongsTo(PpmpItem::class, 'ppmp_item');
    }

}
