<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Match extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'provider_id',
        'sport_type',
        'home_team',
        'guest_team',
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

    public function sport_type(){
        return $this->hasOne('App\Models\SportTypes' , 'type' , 'sport_type');
    }

    public function sport_type_part(){
        return $this->sport_type()->select('name' , 'group' , 'details');
    }

}
