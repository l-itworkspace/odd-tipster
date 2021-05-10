<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'provider_slug',
        'sport_id',
        'tournament_id',
        'category_id',
        'home_team',
        'guest_team',
        'location',
        'start_time'
    ];

    protected $casts = [
        "start_time" => "date:d , H:i",
    ];

    public function odds(){
        return $this->hasMany('App\Models\Odd' , 'match_id' , 'id');
    }

    public function odd(){
        return $this->hasOne('App\Models\Odd' , 'match_id' , 'id');
    }

}
