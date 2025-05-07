<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Deadline extends Model
{
    use HasFactory;

    protected $fillable = [
        'aop_deadline',
        'aop_start_date',
        'ppmp_deadline',
        'ppmp_start_date',
    ];

    protected $casts = [
        'aop_deadline' => 'date',
        'aop_start_date' => 'date',
        'ppmp_deadline' => 'date',
        'ppmp_start_date' => 'date',
    ];
}
