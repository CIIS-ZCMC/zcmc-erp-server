<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
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
     * Get the division chief for this unit
     *
     * @return User|null
     */
    public function getDivisionChief()
    {
        // Get the division this unit belongs to - use the method explicitly
        $division = $this->division()->first();

        if (!$division) {
            return null;
        }

        // The division chief is the head of the division - use method explicitly
        return $division->head()->first();
    }
}
