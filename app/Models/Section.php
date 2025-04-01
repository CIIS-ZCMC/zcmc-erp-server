<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\TransactionLog;
use App\Models\User;

/**
 * Section Model
 * 
 * Represents an organizational section within the system.
 * 
 * @property int $id
 * @property int $head_id Foreign key to users table
 * @property string $name Name of the section
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Section extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'umis_section_id',
        'head_id',
        'oic_id',
        'division_id',
        'department_id',
        'umis_section_id',
        'name',
    ];

    /**
     * Get the user who heads this section.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function head()
    {
        return $this->belongsTo(User::class);
    }

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function units()
    {
        return $this->hasMany(Unit::class);
    }

    /**
     * Get the user that OIC this section.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function oic()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all transaction logs associated with this section.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function logs()
    {
        return $this->morphMany(TransactionLog::class, 'referrence');
    }
}
