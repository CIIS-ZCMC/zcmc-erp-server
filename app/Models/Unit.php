<?php

namespace App\Models;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use App\Models\TransactionLog;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Models\Division;
use App\Models\Section;
use App\Models\AssignedArea;

/**
 * Unit Model
 *
 * Represents an organizational unit within the system.
 *
 * @property int $id
 * @property int $head_id Foreign key to users table
 * @property string $name Name of the unit
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Unit extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'head_id',
        'oic_id',
        'area_id',
        'division_id',
        'section_id',
        'name',
        'code',
    ];

    /**
     * Get the user who heads this unit.
     *
     * @return BelongsTo
     */
    public function head(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    /**
     * Get all transaction logs associated with this unit.
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
     * Get the division chief for this department
     *
     * @return User|null
     */
    public function getDivisionChief(): ?\App\Models\User
    {
        // Get the section this unit belongs to
        $section = $this->section()->first();

        if (!$section) {
            return null;
        }

        // Get the division from the section
        $division = $section->division()->first();

        if (!$division) {
            return null;
        }

        // The division chief is the head of the division
        return $division->head()->first();
    }
}
