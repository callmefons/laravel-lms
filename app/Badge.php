<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    protected $visible = ['id','name', 'image', 'xp'];

    protected $hidden = [
        'created_at', 'updated_at'
    ];

    protected $fillable = [
        'name',
        'image',
        'xp'
    ];

    public function course(){
        return $this->belongsTo('App\Course');
    }

    public function students()
    {
        return $this->belongsToMany('App\Student');
    }
}
