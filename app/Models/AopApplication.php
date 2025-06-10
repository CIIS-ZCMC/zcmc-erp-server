<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class AopApplication extends Model
{

    use HasFactory, SoftDeletes;

    const STATUS_PENDING = 'Pending';
    const STATUS_APPROVED = 'Approved';
    const STATUS_RETURNED = 'Returned';

    const STATUS_IS_DRAFT = 'Draft';

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
        'sector',
        'sector_id',
        'year',
    ];

    protected $casts = [
        'has_discussed' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->aop_application_uuid)) {
                $model->aop_application_uuid = substr(Str::uuid(), 0, 8);
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

    public function applicationTimelines(): HasMany
    {
        return $this->hasMany(ApplicationTimeline::class);
    }

    public function ppmpApplication()
    {
        return $this->hasOne(PpmpApplication::class);
    }
}
