<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Odd extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'match_id',
        'site_slug',
        'site_nickname',
        'win_home',
        'win_guest',
        'draw',
        'last_update'
    ];

    protected $casts = [
        "last_update" => "date:d , H:i",
    ];


    public function Game(){
        $this->hasOne('App\Models\Game' , 'id' , 'match_id');
    }

}
