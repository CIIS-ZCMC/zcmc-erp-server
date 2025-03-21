<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogDescription extends Model
{
    protected $table = "log_descriptions";

    public $fillable = [
        'title',
        'description'
    ];

    public $timestamps = true;

    public function transactionLogs()
    {
        return $this->hasMany( TransactionLog::class);
    }
    
    public function logs()
    {
        return $this->morphMany(TransactionLog::class, 'referrence');
    }
}
