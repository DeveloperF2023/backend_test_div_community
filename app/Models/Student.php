<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'profile_picture',
        'classroom_id',
        'gender',
        'birth_date',
        'cne',
        'level',
        'section',
        'city',
        'phone'
    ];
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }
}
