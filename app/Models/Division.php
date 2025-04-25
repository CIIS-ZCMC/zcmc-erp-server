<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;
use App\Models\TransactionLog;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;


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
        'umis_division_id',
        'name',
    ];

    /**
     * Get the user that heads this division.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function head(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class);
    }

    public function units(): HasMany {
        return $this->hasMany(Unit::class);
    }

    /**
     * Get the transaction logs for this division.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function logs(): MorphMany
    {
        return $this->morphMany(TransactionLog::class, 'referrence');
    }
}
