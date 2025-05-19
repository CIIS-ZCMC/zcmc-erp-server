<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActivityPpmpItem extends Pivot
{
    use SoftDeletes;

    protected $table = 'activity_ppmp_item';

    protected $fillable = [
        'activity_id',
        'ppmp_item_id',
    ];

}
