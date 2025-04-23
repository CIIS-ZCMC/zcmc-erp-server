<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'umis_employee_profile_id',
        'designation_id',
        'division_id',
        'department_id',
        'section_id',
        'unit_id',
        'name',
        'email',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'remember_token',
    ];

    public function logs()
    {
        return $this->morphMany(TransactionLog::class, 'referrence');
    }

    public function assignedArea()
    {
        return $this->hasOne(AssignedArea::class);
    }

    public function comments()
    {
        return $this->hasMany(ActivityComment::class);
    }

    //Temporary solution
    public function budgetOfficer()
    {
        return Section::where('name', 'Budget Section')->first();
    }

    public function designation()
    {
        return $this->belongsTo(Designation::class);
    }
}
