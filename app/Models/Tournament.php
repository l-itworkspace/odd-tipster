<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tournament extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'category_id',
        'name',
        'provider_slug',
    ];

    public function games(){
        return $this->hasMany('App\Models\Game','tournament_id' , 'id');
    }

    public function weekGames(){
        return $this->hasMany('App\Models\Game','tournament_id' , 'id');
    }

    public function gamesToday(){
        $date = \Request::get('date') ?? date('Y-m-d');
        return $this->games()->whereBetween('games.start_time' , [$date . ' 00:00:00' ,  $date . ' 23:59:59'] );
    }

}
