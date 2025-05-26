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
        'id',
        'umis_employee_profile_id',
        'authorization_pin',
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

    public function session()
    {
        return $this->hasOne(AccessToken::class);
    }

    /**
     * Determine if the current API token has a given ability.
     *
     * @param  string  $ability
     * @return bool
     */
    public function tokenCan($ability)
    {
        $token = $this->currentAccessToken();

        if (! $token) {
            return false;
        }

        // Check if the token has wildcard ability
        if (in_array('*', $token->abilities)) {
            return true;
        }

        // Check for specific ability
        return in_array($ability, $token->abilities);
    }

    /**
     * Get the current access token being used by the user.
     *
     * @return \App\Models\AccessToken|null
     */
    public function currentAccessToken()
    {
        return $this->session;
    }

}
