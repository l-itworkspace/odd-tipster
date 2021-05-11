<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SportTypes extends Model
{
    use HasFactory;

    const TYPE_SPORT    = 1;
    const TYPE_CATEGORY = 0;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'slug',
        'active',
        'provider_slug',
        'parent_id',
        'type'
    ];

    public function tournament(){
        return $this->hasMany('App\Models\Tournament' , 'category_id' , 'id');
    }

    public function checkTournament(){
        return $this->hasOne('App\Models\Tournament' , 'category_id' , 'id');
    }

    public function categories(){
        return $this->hasMany('App\Models\SportTypes' , 'parent_id' , 'id');
    }

    public function games(){
        return $this->hasMany('App\Models\Game' , 'category_id' , 'id');
    }

    public function gamesToday(){
        $date = \Request::get('date') ?? date('Y-m-d');
        return $this->games()->whereBetween('games.start_time' , [$date . ' 00:00:00' ,  $date . ' 23:59:59'] );
    }

}
