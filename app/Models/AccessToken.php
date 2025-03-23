<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccessToken extends Model
{
    protected $table = 'access_tokens';

    public $fillable = [
        'session_id',
        'permissions',
        'authorizarition_pin',
        'user_id',
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
