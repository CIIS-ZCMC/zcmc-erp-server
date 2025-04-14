<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PpmpApplication extends Model
{
    use SoftDeletes, HasFactory;

    protected $table = 'ppmp_applications';

    protected $fillable = [
        'aop_application_id',
        'user_id',
        'division_chief_id',
        'budget_officer_id',
        'ppmp_application_uuid',
        'ppmp_total',
        'status',
        'remarks'
    ];

    public $timestamps = true;

    public function aop_application_id()
    {
        return $this->belongsTo(AopApplication::class, 'aop_application_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function divisionChief()
    {
        return $this->belongsTo(User::class, 'division_chief_id');
    }

    public function budgetOfficer()
    {
        return $this->belongsTo(User::class, 'budget_officer_id');
    }

    public function ppmpItem()
    {
        return $this->hasMany(PpmpItem::class);
    }

    public function logs()
    {
        return $this->morphMany(TransactionLog::class, 'referrence');
    }

    public function calculatePpmpTotal()
    {
        return $this->ppmpItem->sum(function ($item) {
            $itemBudget = $item->item->estimated_budget ?? 0;
            $itemTotalAmount = $itemBudget * $item->total_quantity;

            return $itemTotalAmount;
        });

    }
}
