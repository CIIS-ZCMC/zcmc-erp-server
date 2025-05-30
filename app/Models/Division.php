<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'id',
        'head_id',
        'oic_id',
        'area_id',
        'name',
        'code',
    ];

    /**
     * Get the user that heads this division.
     *
     * @return BelongsTo
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
     * @return MorphMany
     */
    public function logs(): MorphMany
    {
        return $this->morphMany(TransactionLog::class, 'referrence');
    }

    /**
     * Get the division ID for this section
     *
     * @return int|null
     */
    public function getDivisionId(): ?int
    {
        // Return the division_id directly from the section model
        return $this->division_id;
    }

    /**
     * Get the division chief for this section
     *
     * @return User|null
     */
    public function getDivisionChief(): ?\App\Models\User
    {
        // Get the division this section belongs to - use the method explicitly
        $division = $this->where('id', $this->getDivisionId())->first();

        if (!$division) {
            return null;
        }

        // The division chief is the head of the division - use method explicitly
        return $division->head()->first();
    }
}
