<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReplyComment extends Model
{
    protected $visible = ['id', 'user_id','comment_id', 'name', 'detail','created_at', 'updated_at'];

    protected $hidden = [
    ];

    protected $fillable = [
        'user_id', 'name', 'post_id', 'detail'
    ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function comment(){
        return $this->belongsTo('App\Comment');
    }

}
