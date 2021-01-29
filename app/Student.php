<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Student extends Model
{
    protected $visible = ['id', 'user_id','student_id', 'course_id','name', 'image', 'overall_xp', 'level', 'username', 'password'];

    protected $hidden = [
        'created_at', 'updated_at'
    ];

    protected $fillable = [
        'user_id',
        'student_id',
        'course_id',
        'name',
        'image',
        'username',
        'password',
        'overall_xp',
        'level'
    ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function course(){
        return $this->belongsTo('App\Course');
    }

    public function badges()
    {
        return $this->belongsToMany('App\Badge');
    }

}
