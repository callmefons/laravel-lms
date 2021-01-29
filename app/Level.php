<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    protected $visible = ['level_id', 'floor_xp', 'ceiling_xp'];

    protected $hidden = [
        'created_at', 'updated_at'
    ];

    protected $fillable = [
        'level_id',
        'floor_xp',
        'ceiling_xp'
    ];

    public function course(){
        return $this->belongsTo('App\Course');
    }
}
