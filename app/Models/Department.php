<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\TransactionLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Models\Division;

/**
 * Department Model
 * 
 * Represents an organizational department within the system.
 * 
 * @property int $id
 * @property int $head_id Foreign key to users table
 * @property string $name Name of the department
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Department extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'umis_department_id',
        'head_id',
        'oid_id',
        'division_id',
        'umis_department_id',
        'name',
    ];

    /**
     * Get the user who heads this department.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function head(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the division where the department belongs.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class);
    }

    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
    }

    /**
     * Get the user that OIC this department.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function oic(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all transaction logs associated with this department.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function logs(): MorphMany
    {
        return $this->morphMany(TransactionLog::class, 'referrence');
    }
}
