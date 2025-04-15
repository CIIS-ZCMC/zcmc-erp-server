<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccessToken extends Model
{
    protected $table = 'access_tokens';

    public $fillable = [
        'user_id',
        'session_id',
        'permissions',
        'authorization_pin',
        'expire_at'
    ];

    public $timestamps = true;

    protected $casts = [
        'permissions' => 'json'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
