<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
        'is_reviewed',
        'cost',
        'start_month',
        'end_month',
        'expense_class'
    ];

    protected $casts = [
        'is_gad_related' => 'boolean',
        'is_reviewed' => 'boolean',
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

    protected static function booted()
    {
        static::deleting(function ($activity) {
            if (!$activity->isForceDeleting()) {
                $activity->resources()->each(function ($resource) {
                    $resource->delete();
                });

                $activity->responsiblePeople()->each(function ($person) {
                    $person->delete();
                });

                $activity->target()?->delete(); // if target is a hasOne or morphOne relationship
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

    public function ppmpItems()
    {
        // return $this->belongsToMany(PpmpItem::class, 'activity_ppmp_item')
        //     ->withPivot('remarks')
        //     ->withTimestamps();

        return $this->belongsToMany(PpmpItem::class, 'activity_ppmp_item')
            ->using(ActivityPpmpItem::class)
            ->withPivot('remarks', 'deleted_at')
            ->withTimestamps()
            ->wherePivotNull('deleted_at');
    }
}
