<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class AopApplication extends Model
{

    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'division_chief_id',
        'mcc_chief_id',
        'planning_officer_id',
        'aop_application_uuid',
        'mission',
        'status',
        'has_discussed',
        'remarks',
    ];

    protected $casts = [
        'has_discussed' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->aop_application_uuid)) {
                $model->aop_application_uuid = Str::uuid();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function divisionChief(): BelongsTo
    {
        return $this->belongsTo(User::class, 'division_chief_id');
    }

    public function mccChief(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mcc_chief_id');
    }

    public function planningOfficer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'planning_officer_id');
    }

    public function applicationObjectives(): HasMany
    {
        return $this->hasMany(ApplicationObjective::class);
    }

    
}
