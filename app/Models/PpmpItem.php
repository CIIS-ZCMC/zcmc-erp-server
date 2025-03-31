<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PpmpItem extends Model
{
    protected $table = 'ppmp_items';

    protected $fillable = [
        'ppmp_application_id',
        'item_id',
        'procurement_modes_id',
        'item_request_id',
        'total_quantity',
        'estimated_budget',
        'total_amount',
        'remarks',
        'comment'
    ];

    public $timestamps = true;

    public function ppmpApplication()
    {
        return $this->belongsTo(PpmpApplication::class, 'ppmp_application_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function procurementMode()
    {
        return $this->belongsTo(ProcurementModes::class, 'procurement_modes_id');
    }

    public function itemRequest()
    {
        return $this->belongsTo(ItemRequest::class, 'item_request_id');
    }

    public function logs()
    {
        return $this->morphMany(TransactionLog::class, 'referrence');
    }
}
