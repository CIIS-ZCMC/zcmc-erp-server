<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityComment extends Model
{
    protected $fillable = [ 
        'activity_id',
        'user_id',
        'comment',
    ];

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
