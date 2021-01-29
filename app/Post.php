<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{

    protected $visible = ['id', 'course_id', 'title', 'detail','created_at', 'updated_at'];

    protected $hidden = [
    ];

    protected $fillable = [
        'course_id', 'title', 'detail'
    ];

    public function course(){
        return $this->belongsTo('App\Course');
    }

    public function comments()
    {
        return $this->hasMany('App\Comment');
    }



}
