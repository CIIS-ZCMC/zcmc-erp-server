<?php

namespace App\Auth;

use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthUserProvider implements UserProvider
{
    protected $model;

    public function __construct($model)
    {
        $this->model = $model;
    }

    public function retrieveById($identifier)
    {
        return $this->model::find($identifier);
    }

    public function retrieveByToken($identifier, $token)
    {
        return $this->model::where('id', $identifier)->where('remember_token', $token)->first();
    }

    public function updateRememberToken(UserContract $user, $token)
    {
        $user->setRememberToken($token);
        $user->save();
    }

    public function retrieveByCredentials(array $credentials)
    {
        return $this->model::where('email', $credentials['email'])->first();
    }

    public function validateCredentials(UserContract $user, array $credentials)
    {
        return Hash::check($credentials['password'], $user->getAuthPassword());
    }

    /**
     * Rehash the user's password if required.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  array  $credentials
     * @param  bool  $force
     * @return void
     */
    public function rehashPasswordIfRequired(UserContract $user, array $credentials, bool $force = false)
    {
        // Check if the password needs to be rehashed
        if ($force || $this->needsRehash($user->getAuthPassword())) {
            $user->setAuthPassword($credentials['password']);
            $user->save();
        }
    }

    /**
     * Determine if the given password needs to be rehashed.
     *
     * @param  string  $hashedPassword
     * @return bool
     */
    protected function needsRehash($hashedPassword)
    {
        // Use Laravel's built-in password_needs_rehash function
        return password_needs_rehash($hashedPassword, PASSWORD_DEFAULT);
    }
}