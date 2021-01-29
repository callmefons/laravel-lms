<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{

    protected $visible = ['id', 'user_id','post_id', 'name', 'detail','created_at', 'updated_at'];

    protected $hidden = [
    ];

    protected $fillable = [
        'user_id', 'name', 'post_id', 'detail'
    ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function post(){
        return $this->belongsTo('App\Post');
    }

    public function reply_comments()
    {
        return $this->hasMany('App\ReplyComment');
    }

}
