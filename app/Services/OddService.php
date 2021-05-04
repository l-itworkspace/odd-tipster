<?php

namespace App\Services;

use App\Models\Match;
use App\Models\Odd;
use App\Services\Base\ApiService;


use App\Models\SportTypes;

class OddService extends ApiService
{

    public function __construct($credentials)
    {
        $this->credentials = $credentials;
    }

    public function getSportTypes($get_all = [] , $select = ['name', 'group', 'details', 'type', 'active', 'parent_id'])
    {
        $types_response = [];

        if ((isset($get_all['api']) && $get_all['api']) || !$get_all) {
            $types_response['api'] = $this->withApiToken('get', 'sports');
        }

        if ((isset($get_all['db']) && $get_all['db']) || !$get_all) {
            $types_response['db'] = SportTypes::where('active', 1)->get($select);
        }

        if ($get_all) {
            return $types_response['db'] ?? $types_response['api'];
        }

        return $types_response;
    }

    public function updateSportTypes()
    {
        $sport_types        = $this->getSportTypes();
        $exists = $sport_types['db']->toArray();
        $insert_into = [];
        $cr_date = date('Y-m-d H:i:s');

        foreach ($sport_types['api']['data'] as $k => $type_api) {

            foreach ($exists as $key => $ex_types) {
                if ($ex_types['type'] === $type_api['key']) break;
            }

            $insert_or_update = [
                'type'    => trim($type_api['key']),
                'details' => $type_api['details'],
                'name'    => $type_api['title'],
                'group'   => $type_api['group'],
                'active'  => $type_api['active']
            ];

            if (isset($ex_types)) {
                try {
                    SportTypes::where('type', trim($type_api['key']))->update($insert_or_update);
                } catch (\Exception $e) {
                    return ['success' => false, 'message' => $e->getMessage()];
                }
                continue;
            }

            $insert_into[] = array_merge($insert_or_update, ['created_at' => $cr_date, 'updated_at' => $cr_date]);
        }

        if ($insert_into) {
            return ['success' => SportTypes::insert($insert_into)];
        }
        return ['success' => true];
    }

    public function getMatches($get_all = [] , $sport_type = null )
    {
        $matches_response = [];

        if((isset($get_all['api']) && $get_all['api']) || !$get_all){

            $sport_types = $this->withApiToken('get', 'odds', ['sport' => $sport_type, 'region' => 'uk' ]);

            if($sport_types['success']){
                $matches_response['api'] = $sport_types['data'];
            }else{
                $matches_response['api'] = $sport_types;
            }

        }

       if ((isset($get_all['db']) && $get_all['db']) || !$get_all) {
           $selects = ['*'];

           if(isset($get_all['db']['select'])){
               $selects = $get_all['db']['select'];
           }

           $matches = Match::select($selects);

           if(isset($get_all['db']['where'])){
               $matches->where($get_all['db']['where']);
           }

           if(isset($get_all['db']['with_odds']) && $get_all['db']['with_odds']){
               $matches->with('odds');
           }

           if(isset($get_all['db']['with_odd']) && $get_all['db']['with_odd']){
               $matches->with('odd');
           }

           if(isset($get_all['db']['with_type']) && $get_all['db']['with_type'] ){
               $matches->with('sport_type');
           }
           if(isset($get_all['db']['with_type_part']) && $get_all['db']['with_type_part'] ){
               $matches->with('sport_type_part');
           }

           if($sport_type){
               $matches->where('sport_type' , $sport_type);
           }



           $matches_response['db'] = $matches->paginate(20)->appends(\Request::all());
       }

        if($get_all){
            return $matches_response['db'] ?? $matches_response['api'];
        }
        return $matches_response;
    }

    public function updateMatches(){
        $sport_types = $this->getSportTypes( ['db' => true], ['type'])->toArray();
        $datas = [
            'db' => [
                'where' => [
                    ['created_at' , '>=' , date('Y-m-d H:i:s' , strtotime('-5 hour'))]
                ],
                'with_odds' => true,
                ],
        ];

        $matches = $this->getMatches(  $datas );
//        array_unshift($sport_types , ['type' => 'upcoming']);


        $cr_date = date('Y-m-d H:i:s');
        $delete_me = microtime(true);
        foreach(array_chunk($sport_types , 3) as $k => $sport_types_chunked){
            $inserts = [];
            $odds    = [];
            // I dont want to collect my inserts array
            $provider_ids = [];
            foreach ($sport_types_chunked as $chunk_key => $sport_type ){

                $matches_from_api = $this->getMatches(['api'=> true] , $sport_type['type']);

                foreach ($matches_from_api as $key => $match_api){
                    if(!$match_api){
                        \Log::info('Api expire');
                        die;
                    }
                    foreach ($matches as $m_key => $match){
                        if($match['provider_id'] === $match_api['id']) break;
                    }

                    // Here I am really sure that the key will be exists.
                    $guest_team = array_search($match_api['home_team'] ,  $match_api['teams']);
                    $guest_team_index =  (int) !$guest_team;

                    if(isset($match)){
                        foreach ($match->odds as $ex_match_k => $match_odd){
                            foreach($match_api['sites'] as $api_k => $match_site){
                               if($match_odd['site_slug'] === $match_site['site_key']) break;
                            }
                            $odds_h2h = $match_site['odds']['h2h'];
                            $match_odd->update([
                                'last_update' => date('Y-m-d H:i:s' , $match_site['last_update']),
                                'win_home'    => $odds_h2h[!$guest_team_index],
                                'win_guest'   => $odds_h2h[$guest_team_index],
                                'draw'        => $odds_h2h[2] ?? null,
                            ]);
                        }
                    }else{
                        $inserts[] =[
                            'provider_id' => $match_api['id'],
                            'sport_type'  => $match_api['sport_key'],
                            'home_team'   => $match_api['home_team'],
                            'guest_team'  => $match_api['teams'][$guest_team_index],
                            'start_time'  => date('Y-m-d H:i:s' , $match_api['commence_time']),
                            'created_at'  => $cr_date,
                            'updated_at'  => $cr_date
                        ];
                        $provider_ids[] = $match_api['id'];
                        foreach ($match_api['sites'] as $s_key => $site){
                            if(!isset($odds[$match_api['id']])){
                                $odds[$match_api['id']] = [];
                            }
                            $odds_h2h = $site['odds']['h2h'];

                            $odds[$match_api['id']][] = [
                                'site_slug'     => $site['site_key'],
                                'site_nickname' => $site['site_nice'],
                                'win_home'      => $odds_h2h[!$guest_team_index],
                                'win_guest'     => $odds_h2h[$guest_team_index],
                                'draw'          => $odds_h2h[2] ?? null,
                                'last_update'   => date('Y-m-d H:i:s' , $site['last_update']),
                                'created_at'    => $cr_date,
                                'updated_at'    => $cr_date
                            ];
                        }

                    }
                }

            }

            if(Match::insert($inserts)){
                $insert_ids = Match::whereIn('provider_id' ,$provider_ids )->get(['id' , 'provider_id'])->toArray();
                $insert_odds = [];
                foreach ($odds as $o_key => $odd){
                    foreach ($insert_ids as $i_key => $insert_data){
                        if($insert_data['provider_id'] === $o_key) break;
                    }
                    if(isset($insert_data)){
                        foreach ($odd as $odd_key => $odd_insert){
                            $insert_odds[] = array_merge(['match_id' => $insert_data['id']] , $odd_insert);
                        }
                    }
                }
                if(!Odd::insert($insert_odds)){
                    \Log::info('asdas');
                    die;
                };
            }

        }

    }


    public function getOddsByMatchId($id , array $where = []){
        $odds = Odd::where('match_id' , $id );
        if($where){
            $odds->where($where);
        }

        return $odds->get(['site_slug' , 'site_nickname' , 'win_home' , 'win_guest' , 'draw' , 'last_update']);
    }

    public function withApiToken($method, $url, $data = [])
    {
        return $this->requestTo($method, $url, array_merge($data, ['apiKey' => $this->credentials['key']]), true);
    }
}
