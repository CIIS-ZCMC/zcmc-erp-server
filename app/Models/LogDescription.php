<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogDescription extends Model
{
    protected $table = "log_descriptions";

    public $fillable = [
        'title',
        'code',
        'description'
    ];

    public $timestamps = true;

    protected $casts = ['deleted_at' => 'datetime'];

    public function transactionLogs()
    {
        return $this->hasMany( TransactionLog::class);
    }
}
