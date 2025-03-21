<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionLog extends Model
{
    protected $table = "transaction_logs";

    public $fillable = [
        'user_id',
        'log_description_id',
        'user_name',
        'referrence_id',
        'referrance_table',
        'metadata',
        'issue'
    ];

    public $timestamps = true;
}
