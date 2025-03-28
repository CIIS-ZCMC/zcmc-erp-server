<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Division extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'umis_division_id',
        'head_id',
        'oic_id',
        'name',
    ];

    /**
     * Get the user that heads this division.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function head()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user that OIC this division.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function oic()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get the transaction logs for this division.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function logs()
    {
        return $this->morphMany(TransactionLog::class, 'referrence');
    }
}
