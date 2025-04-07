<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Activity extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'application_objective_id',
        'activity_uuid',
        'activity_code',
        'name',
        'is_gad_related',
        'cost',
        'start_month',
        'end_month',
    ];

    protected $casts = [
        'is_gad_related' => 'boolean',
        'cost' => 'float',
        'start_month' => 'date',
        'end_month' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->activity_uuid)) {
                $model->activity_uuid = Str::uuid();
            }
        });
    }

    public function applicationObjective(): BelongsTo
    {
        return $this->belongsTo(ApplicationObjective::class);
    }

    public function target(): HasOne
    {
        return $this->hasOne(Target::class);
    }

    public function resources(): HasMany
    {
        return $this->hasMany(Resource::class);
    }

    public function responsiblePeople(): HasMany
    {
        return $this->hasMany(ResponsiblePerson::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ActivityComment::class);
    }
}
