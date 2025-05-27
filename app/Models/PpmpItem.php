<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PpmpItem extends Model
{
    use SoftDeletes;

    protected $table = 'ppmp_items';

    protected $fillable = [
        'ppmp_application_id',
        'item_id',
        'procurement_mode_id',
        'item_request_id',
        'total_quantity',
        'estimated_budget',
        'total_amount',
        'expense_class',
        'remarks',
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
        return $this->belongsTo(ProcurementModes::class, 'procurement_mode_id');
    }

    public function itemRequest()
    {
        return $this->belongsTo(ItemRequest::class, 'item_request_id');
    }

    public function activities()
    {
        // return $this->belongsToMany(Activity::class);
        return $this->belongsToMany(Activity::class, 'activity_ppmp_item')
            ->using(ActivityPpmpItem::class)
            ->withPivot('remarks', 'is_draft', 'deleted_at')
            ->withTimestamps()
            ->wherePivotNull('deleted_at');
    }

    public function comments()
    {
        return $this->hasMany(PpmpItemComment::class, 'ppmp_item_id');
    }

    public function ppmpSchedule()
    {
        return $this->hasMany(PpmpSchedule::class);
    }

    public function logs()
    {
        return $this->morphMany(TransactionLog::class, 'referrence');
    }

    public function scopeSearch($query, array $terms)
    {
        return $query->where(function ($q) use ($terms) {
            foreach ($terms as $term) {
                $q->where('total_quantity', 'like', "%{$term}%")
                    ->orWhere('estimated_budget', 'like', "%{$term}%")
                    ->orWhere('total_amount', 'like', "%{$term}%")
                    ->orWhere('remarks', 'like', "%{$term}%")
                    ->orWhereHas('item', function ($q) use ($term) {
                        $q->where('name', 'like', "%{$term}%")
                            ->orWhere('code', 'like', "%{$term}%")
                            ->orWhere('estimated_budget', 'like', "%{$term}%")
                            ->orWhereHas('itemUnit', function ($q) use ($term) {
                                $q->where('name', 'like', "%{$term}%")
                                    ->orWhere('code', 'like', "%{$term}%")
                                    ->orWhere('description', 'like', "%{$term}%");
                            })
                            ->orWhereHas('itemCategory', function ($q) use ($term) {
                                $q->where('name', 'like', "%{$term}%")
                                    ->orWhere('code', 'like', "%{$term}%")
                                    ->orWhere('description', 'like', "%{$term}%");
                            })
                            ->orWhereHas('itemClassification', function ($q) use ($term) {
                                $q->where('name', 'like', "%{$term}%")
                                    ->orWhere('code', 'like', "%{$term}%")
                                    ->orWhere('description', 'like', "%{$term}%");
                            })
                            ->orWhereHas('itemSpecifications', function ($q) use ($term) {
                                $q->where('description', 'like', "%{$term}%");
                            });
                    })
                    ->orWhereHas('procurementMode', function ($q) use ($term) {
                        $q->where('name', 'like', "%{$term}%");
                    })
                    ->orWhereHas('itemRequest', function ($q) use ($term) {
                        $q->where('name', 'like', "%{$term}%")
                            ->orWhere('code', 'like', "%{$term}%")
                            ->orWhere('estimated_budget', 'like', "%{$term}%")
                            ->orWhere('status', 'like', "%{$term}%")
                            ->orWhere('reason', 'like', "%{$term}%");
                    })
                    ->orWhereHas('activities', function ($q) use ($term) {
                        $q->where('activity_code', 'like', "%{$term}%")
                            ->orWhere('name', 'like', "%{$term}%")
                            ->orWhere('cost', 'like', "%{$term}%")
                            ->orWhere('start_month', 'like', "%{$term}%")
                            ->orWhere('end_month', 'like', "%{$term}%");
                    })
                    ->orWhereHas('comments', function ($q) use ($term) {
                        $q->where('comment', 'like', "%{$term}%");
                    })
                    ->orWhereHas('ppmpSchedule', function ($q) use ($term) {
                        $q->where('quantity', 'like', "%{$term}%")
                            ->orWhere('month', 'like', "%{$term}%")
                            ->orWhere('year', 'like', "%{$term}%");
                    });
            }
        });
    }
}
