<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogDescription extends Model
{
    protected $table = "log_descriptions";

    public $fillable = [
        'title',
        'code',
        'description',
        'deleted_at'
    ];

    public $timestamps = true;

    public function transactionLogs()
    {
        return $this->hasMany( TransactionLog::class);
    }
}
