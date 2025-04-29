<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\TransactionLog;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Models\Division;
use App\Models\Section;

/**
 * Unit Model
 * 
 * Represents an organizational unit within the system.
 * 
 * @property int $id
 * @property int $head_id Foreign key to users table
 * @property string $name Name of the unit
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
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
        'division_id',
        'section_id',
        'name',
        'code',
    ];

    /**
     * Get the user who heads this unit.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
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
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function logs(): MorphMany
    {
        return $this->morphMany(TransactionLog::class, 'referrence');
    }
}
