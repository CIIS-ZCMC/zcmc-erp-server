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
        'head_id',
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
