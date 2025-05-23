<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\TransactionLog;
use App\Models\User;
use App\Models\AssignedArea;
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
        'id',
        'head_id',
        'oid_id',
        'area_id',
        'division_id',
        'name',
        'code',
    ];

    /**
     * Get the user who heads this department.
     *
     * @return BelongsTo
     */
    public function head(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the division where the department belongs.
     *
     * @return BelongsTo
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
     * @return BelongsTo
     */
    public function oic(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the division chief for this department
     *
     * @return User|null
     */
    public function getDivisionChief(): ?User
    {
        // Get the division this department belongs to - use the method explicitly
        $division = $this->division()->first();

        if (!$division) {
            return null;
        }

        // The division chief is the head of the division - use method explicitly
        return $division->head()->first();
    }

    /**
     * Get all transaction logs associated with this department.
     *
     * @return MorphMany
     */
    public function logs(): MorphMany
    {
        return $this->morphMany(TransactionLog::class, 'referrence');
    }
}
