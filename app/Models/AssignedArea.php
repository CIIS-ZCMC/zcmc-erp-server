<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use App\Models\TransactionLog;
use App\Models\User;
use App\Models\Division;
use App\Models\Department;
use App\Models\Section;
use App\Models\Unit;
use App\Http\Resources\AssignAreaDivisionResource;
use App\Http\Resources\AssignAreaDepartmentResource;
use App\Http\Resources\AssignAreaSectionResource;
use App\Http\Resources\AssignAreaUnitResource;
use Illuminate\Database\Eloquent\Model;

/**
 * AssignedArea Model
 *
 * Represents an assignment of users to organizational areas (division, department, section, unit).
 *
 * @property int $id
 * @property int $division_id Foreign key to divisions table
 * @property int $department_id Foreign key to departments table
 * @property int $section_id Foreign key to sections table
 * @property int $unit_id Foreign key to units table
 * @property int $user_id Foreign key to users table
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class AssignedArea extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'assigned_areas';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'user_id',
        'designation_id',
        'division_id',
        'department_id',
        'section_id',
        'unit_id',
    ];

    /**
     * Get the division associated with this assigned area.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    /**
     * Get the department associated with this assigned area.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the section associated with this assigned area.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    /**
     * Get the unit associated with this assigned area.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function findDetails()
    {
        if ($this->division_id !== null) {
            return [
                'details' => new AssignAreaDivisionResource($this->division),
                'sector' => 'Division'
            ];
        }

        if ($this->department_id !== null) {
            return [
                'details' => new AssignAreaDepartmentResource($this->department),
                'sector' => 'Department'
            ];
        }

        if ($this->section_id !== null) {
            return [
                'details' => new AssignAreaSectionResource($this->section),
                'sector' => 'Section'
            ];
        }

        return [
            'details' => new AssignAreaUnitResource($this->unit),
            'sector' => 'Unit'
        ];
    }

    /**
     * Get the user associated with this assigned area.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function designation()
    {
        return $this->belongsTo(Designation::class);
    }

    /**
     * Get all transaction logs associated with this assigned area.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function logs()
    {
        return $this->morphMany(TransactionLog::class, 'referrence');
    }
}
