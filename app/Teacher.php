<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    protected $visible = ['id', 'name',
        'image','title','name',
        'position','id_card','phone','address',
        'teaching_level','institution','province',
        'activated', 'archived'];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'image','title','name',
        'position','id_card','phone','address',
        'teaching_level','institution','province'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function user(){
        return $this->belongsTo('App\User');
    }

    public function courses()
    {
        return $this->hasMany('App\Course');
    }
}
