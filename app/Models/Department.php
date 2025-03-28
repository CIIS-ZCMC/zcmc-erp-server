<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\TransactionLog;  
use App\Models\User;

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
        'head_id',
        'name',
    ];  

    /**
     * Get the user who heads this department.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function head()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all transaction logs associated with this department.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function logs()
    {
        return $this->morphMany(TransactionLog::class, 'referrence');
    }
}
