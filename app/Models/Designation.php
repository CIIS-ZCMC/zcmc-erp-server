<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\TransactionLog;
use App\Models\AssignedArea;

/**
 * Designation Model
 * 
 * Represents a job designation or position within the system.
 * 
 * @property int $id
 * @property string $name Name of the designation
 * @property string $code Code identifier for the designation
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Designation extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id',
        'name',
        'code',
        'probation'
    ];

    public function assignedAreas()
    {
        return $this->hasMany(AssignedArea::class);
    }

    /**
     * Get all transaction logs associated with this designation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function logs()
    {
        return $this->morphMany(TransactionLog::class, 'referrence');
    }
}
