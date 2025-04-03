<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PpmpSchedule extends Model
{
    protected $table = 'ppmp_schedules';

    protected $fillable = [
        'ppmp_item_id',
        'month',
        'year',
        'quantity'
    ];

    public $timestamps = true;

    public function ppmpItem()
    {
        return $this->belongsTo(PpmpItem::class, 'ppmp_item_id');
    }

    public function logs()
    {
        return $this->morphMany(TransactionLog::class, 'referrence');
    }
}
