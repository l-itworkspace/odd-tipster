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

}
