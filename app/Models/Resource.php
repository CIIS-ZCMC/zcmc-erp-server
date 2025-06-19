<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Resource extends Model
{
    use HasFactory;

    protected $fillable = [
        'activity_id',
        'item_id',
        'purchase_type_id',
        // 'object_category',
        'quantity',
        'expense_class',
        'item_cost'

    ];

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function purchaseType(): BelongsTo
    {
        return $this->belongsTo(PurchaseType::class);
    }
}
