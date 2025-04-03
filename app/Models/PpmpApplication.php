<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PpmpApplication extends Model
{
    use SoftDeletes;

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

    // public function aop_application_id()
    // {
    //     return $this->belongsTo(AopApplication::class, 'aop_application_id');
    // }

    public function user_id()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function division_chief_id()
    {
        return $this->belongsTo(User::class, 'division_chief_id');
    }

    public function budget_officer_id()
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
}
