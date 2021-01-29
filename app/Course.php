<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $visible = ['id', 'name',
        'description', 'start_xp',
        'leader_board','status'];

    protected $hidden = [
        'created_at', 'updated_at'
    ];

    protected $fillable = [
        'name',
        'description',
        'start_xp',
        'leader_board',
        'status'
    ];

    public function teacher(){
        return $this->belongsTo('App\Teacher');
    }


    public function students()
    {
        return $this->hasMany('App\Student');
    }

    public function levels()
    {
        return $this->hasMany('App\Level');
    }

    public function badges()
    {
        return $this->hasMany('App\Badge');
    }

    public function posts()
    {
        return $this->hasMany('App\Post');
    }

}
