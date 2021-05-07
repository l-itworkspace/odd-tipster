<?php

namespace App\Services;

use App\Models\SportTypes;
use App\Services\Base\ApiService;

class PinnacleService extends ApiService{

    public function check(){
        return $this->requestTo('GET' , 'sports' , [] , true);
    }

    public function getSportTypes(){
        return $this->requestTo('GET' , 'sports' , [] , true);
    }

    public function insertSportTypes(){
        $types = $this->getSportTypes();
        $exists = SportTypes::select('provider_id' , 'id')->get();
        $insert = [];
        $cr_date = date('Y-m-d H:i:s');
        foreach ( $types as $t_key => $type ){
            $create_or_update = [
                'name'        => $type['name'],
                'slug'        => \Str::slug($type['name']),
                'active'      => !$type['isHidden'],
                'provider_id' => $type['id'],
                'created_at'  => $cr_date,
                'updated_at'  => $cr_date
            ];

            if($to_update = $exists->where('provider_id' , $type['id'])->first()){
                $to_update->update($create_or_update);
            }else{
                $insert[] = $create_or_update;
            }
        }

        if($insert){
            SportTypes::insert($insert);
        }

    }

    public function getGames(){
        $x_api_key = \Session::get('x-api-key') ?: \Str::random(32);

        if(!\Session::has('x-api-key')){
             \Session::put('x-api-key' , $x_api_key);
        }
//        $mucha =  $this->requestTo('GET' , 'sports/29/markets/highlighted/straight?primaryOnly=false');
        dd($this->requestTo('GET' , 'sports/29/leagues?all=false'));
        dd($mucha);
        foreach ($mucha as $k => $m){
            if($m['matchupId'] == 1315404875){
                dd($m);
            }
        }
    }

}
