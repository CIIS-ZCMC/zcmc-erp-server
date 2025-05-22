<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Log extends Model
{
    use HasFactory;

    protected $fillable = [
        'aop_application_id',
        'ppmp_application_id',
        'action',
        'action_by',
    ];

    public function aopApplication()
    {
        return $this->belongsTo(AopApplication::class);
    }

    public function ppmpApplication()
    {
        return $this->belongsTo(PpmpApplication::class);
    }

    public function actionByUser()
    {
        return $this->belongsTo(User::class, 'action_by');
    }
}
