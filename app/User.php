<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $visible = ['id', 'email', 'activated', 'archived'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email', 'password','role', 'activated'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function student(){
        return $this->hasOne('App\Student');
    }

    public function teacher(){
        return $this->hasOne('App\Teacher');
    }


    public function role()
    {
        return $this->hasOne('App\Role');
    }
}
