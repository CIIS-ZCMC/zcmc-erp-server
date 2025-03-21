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
        'referrance_type',
        'metadata',
        'issue'
    ];

    public $timestamps = true;

    protected $casts = [
        'metadata' => 'json',
        'issue' => 'json'
    ];

    public function logDescription()
    {
        return $this->belongsTo(LogDescription::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function logs()
    {
        return $this->morphMany(TransactionLog::class, 'referrence');
    }
}
