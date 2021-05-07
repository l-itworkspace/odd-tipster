<?php

namespace App\Services;

use App\Models\Bookmakers;
use App\Models\Game;
use App\Models\Odd;
use App\Models\SportTypes;
use App\Models\Tournament;
use App\Services\Base\ApiService;
use App\Services\Base\OddApiService;

// Here Didnt used EloquentModel::query()
// Please Everywhere change it as can ,
// IN HEROKU CANT WRITE LIKE ::query that is why everywhere it written so stupid

class SportTraderService extends ApiService implements OddApiService {

    public function __construct($credentials)
    {
        $this->credentials  = $credentials;
        $this->app_key_name = 'api_key';
    }

    public function check(){
        $schedules = $this->insertMatches();
    }

    public function getTournaments($get_all = []){
        $types_response = [];

        $select = ['*'];

        if ((isset($get_all['api']) && $get_all['api']) || !$get_all) {
            $tournaments = $this->withApiToken('tournaments.json' );
            if(isset($tournaments['tournaments'])){
                $types_response['api'] = $tournaments['tournaments'];
            }
        }

        if ((isset($get_all['db']) && $get_all['db']) || !$get_all) {
            if(isset($get_all['db']['wheres']) && $get_all['db']['wheres']){
                $wheres = $get_all['db']['wheres'];
            }
            if(isset($get_all['db']['select']) && $get_all['db']['select'] && is_array($get_all['db']['select'])){
                $select = $get_all['db']['select'];
            }

            if(isset($wheres)){
                //Yes you are rigth , But in heroku i cant write ::query();
                if(isset($get_all['db']['whereHas'])){
                    $types_response['db'] = Tournament::whereHas('games')->with('games.odd')->where($wheres)->get($select);
                }else{
                    $types_response['db'] = Tournament::with('games.odd')->where($wheres)->get($select);
                }
            }else{
                if(isset($get_all['db']['whereHas'])){
                    $types_response['db'] = Tournament::whereHas('games')->with('games.odd')->get($select);
                }else{
                    if(isset($get_all['db']['without_rel'])){
                        $types_response['db'] = Tournament::select($select)->get();
                    }else{
                        $types_response['db'] = Tournament::with('games.odd')->get($select);
                    }
                }
            }
        }

        if ($get_all && (!isset($get_all['db']) || !isset($get_all['api']))) {
            return $types_response['db'] ?? $types_response['api'];
        }

        return $types_response;
    }

    public function insertTournaments(){
        $tournaments = $this->getTournaments(
            ['api' => true ,
                'db' => [
                    'without_rel' => true,
                ]]
        );
       if(isset($tournaments['api'])){
            $inserts = [];
            $cr_date = date('Y-m-d H:i:s');
            $selects = [
                'db' => [
                    'wheres' => [
                        ['type' , '=' , SportTypes::TYPE_CATEGORY]
                    ]
                ]
            ];
            $sport_types = $this->getSportTypes($selects);

            foreach ($tournaments['api'] as $k => $tournament){
                $cat = $sport_types->where('provider_slug' , $tournament['category']['id'])->first();
                if(!$cat) continue;
                $insert_or_update = [
                    'name'          => $tournament['name'],
                    'provider_slug' => $tournament['id'],
                    'category_id'   => $cat->id
                ];

                if($ex_tournament = $tournaments['db']->where('provider_slug' , $tournament['id'])->first()){
                    $ex_arr = $ex_tournament->toArray();
                    $diff = array_keys(array_diff($ex_arr , $insert_or_update));
                    if(array_diff($diff , ['id' , 'created_at' , 'updated_at'])){
                        $ex_tournament->update($insert_or_update);
                    }
                    continue;
                }
                $inserts[] = array_merge($insert_or_update , ['created_at' => $cr_date , 'updated_at' => $cr_date]);
            }

            if($inserts){
                Tournament::insert($inserts);
            }
        }
    }

    public function sportEvents(){
        dd($this->withApiToken('sport_events/sr:match:9616117/timeline.json'));
    }

    public function getSportTypes($get_all = []){
        $types_response = [];

        $select = ['*'];

        $wheres  = [['active' , '=' , true]];

        if(isset($get_all['db']['select']) && $get_all['db']['select'] && is_array($get_all['db']['select'])){
            $select = $get_all['db']['select'];
        }

        if ((isset($get_all['api']) && $get_all['api']) || !$get_all) {
            $sport_types = $this->withApiToken( 'sports.json');
            if(isset($sport_types['sports'])){
                $types_response['api'] = $sport_types['sports'];
            }
        }

        if ((isset($get_all['db']) && $get_all['db']) || !$get_all) {
            if(isset($get_all['db']['wheres']) && $get_all['db']['wheres']){
                $wheres = $get_all['db']['wheres'];
            }
            $types_response['db'] = SportTypes::where($wheres)->orderBy('parent_id')->get($select);
        }

        if ($get_all) {
            return $types_response['db'] ?? $types_response['api'];
        }

        return $types_response;
    }

    public function insertSportTypes($call_categories = false){

        $sport_types = $this->getSportTypes();
        $this->sport_types = $sport_types;
        $inserts = [];
        $cr_date = date('Y-m-d');
        foreach ($sport_types['api'] as $s_key => $sport_type){
            $insert_or_update = [
                'provider_slug' => $sport_type['id'],
                'name'          => $sport_type['name'],
            ];

            if($exists_sport = $sport_types['db']->where('provider_slug' , $sport_type['id'])->first()){
                if(!$exists_sport->update($insert_or_update)){
                    // In the future it will be set in cache , and will be worked if you want;
                    $update_errors[] = ['id' => $exists_sport->id , 'data' => $insert_or_update];
                }
            }else{
                $slug  = \Str::slug($sport_type['name']) . '-';
                $insert_or_update = array_merge($insert_or_update , ['created_at' => $cr_date , 'updated_at' => $cr_date , 'slug' => $slug . getUnique(100 - strlen($slug))]);
                $inserts[] = $insert_or_update;
            }
        }

        if($inserts){
            if(!$insert = SportTypes::insert($inserts)){
               $insert_error = $insert;
            }
        }

        $resp = ['success' => true];

        if(isset($update_errors) || isset($insert_error)){
            $resp['success'] = false;

            if(isset($update_errors)){
                $resp['update'] = $update_errors;
            }

            if(isset($insert_error)){
                $resp['insert'] = $insert_error;
            }
        }

        if($resp['success'] && $call_categories){
            $this->insertCategories();
        }

        return $resp;
    }

    public function getCategories(){
        return $this->withApiToken('categories.json');
    }

    public function insertCategories(){
        $categories_api = $this->getCategories();

        $sport_types = $this->getSportTypes(['db'=>['select' => ['id' , 'provider_slug']]]);

        $inserts = [];
        $cr_date = date('Y-m-d H:i:s');
        foreach($categories_api['categories'] as $k => $category){

            $insert_or_update = [
                'name'          => $category['name'],
                'provider_slug' => $category['id'],
                'type'          => SportTypes::TYPE_CATEGORY
            ];
            if($ex_cat = $sport_types->where('provider_slug' , $category['id'])->first()){
                if(!in_array('id' , array_keys(array_diff($ex_cat->toArray() , $insert_or_update)))){
                    $ex_cat->update($insert_or_update);
                }
                continue;
            }

            if(!isset($ex_cat) && ($ex_sport = $sport_types->where('provider_slug' , $category['sport_id'])->first())){
                $insert_or_update['parent_id'] = $ex_sport->id;
            }
            $slug  = \Str::slug($category['name']) . '-';
            $inserts[] = array_merge($insert_or_update , ['created_at' => $cr_date , 'updated_at' => $cr_date , 'slug' => $slug . getUnique(100 - strlen($slug))]);
        }

        if($inserts){
            SportTypes::insert($inserts);
        }
    }

    public function getMatchesDB($get_all = []){
        $selects = ['*'];

        if($get_all){

        }

        return Game::with(['odds.bookmaker'])->select($selects)->get();
    }

    public function getMatches($sport_slug = null , $date = false){

        $matches = $this->withApiToken('sports/' . $sport_slug . '/' . ($date ?: date('Y-m-d' )) . '/schedule.json');

        if( isset($matches['sport_events'])){
            return $matches['sport_events'];
        }

        return false;
    }

    public function insertMatches( $date = false ){
        $selects = [
            'sport_types' => [
                'db' => [
                    'wheres' => [
                        ['active' ,'=',1],
                        ['type' , '=' , SportTypes::TYPE_SPORT]
                    ],
                    'select' => ['id' , 'provider_slug']
                ]
            ],
            'tournaments' => [
                'db' => [
                    'select' => ['id','provider_slug']
                ]
            ],
            'matches' => ['db' => true]
        ];

        $sport_types = $this->getSportTypes($selects['sport_types']);
        $tournaments = $this->getTournaments($selects['tournaments']);

        $exists_matches  = $this->getMatchesDB();

        $time = microtime(true);
        $cr_date = date('Y-m-d H:i:s');
        foreach ($sport_types as $sport_k => $sport_type){
            if($matches = $this->getMatches($sport_type->provider_slug , ($date ?: date('Y-m-d')))){
                \Log::info('After get matches' . $date);
                $inserts     = [];
                $ods_inserts = [];
                $provider_ids =[];
                foreach ($matches as $m_k => $match){

                    if($match['status'] === 'closed') continue;

                    $tournament = $tournaments->where('provider_slug' , $match['tournament']['id'])->first();

                    if(!$tournament) continue;

                    $index_home = !array_search('home' , $match['competitors'][0]);

                    $insert_or_update = [
                        'provider_slug' => $match['id'],
                        'tournament_id' => $tournament->id,
                        'location'      => $match['venue']['name'] ?? null,
                        'start_time'    => $match['scheduled'],
                        'home_team'     => $match['competitors'][$index_home]['name'],
                        'guest_team'    => $match['competitors'][!$index_home]['name']
                    ];

                    $odds = false;
                    foreach ($match['markets'] as $m_key => $market){
                        if($market['name'] === '3way'){
                            $odds = $market['books'];
                            break;
                        }
                    }

                    if($ex_match = $exists_matches->where('provider_slug' , $match['id'])->first()){
                            $time = microtime(true);
                        foreach($ex_match->odds as $k_ex_odd => $odd){
                            foreach($odds as $o_key => $new_odd){
                                if($odd->bookmaker->provider_slug === $new_odd['id']) break;
                            }
                            $indexes_odds = [];
                            foreach ($new_odd['outcomes'] as $out_k => $outcome){
                                $indexes_odds[$outcome['type']] = $outcome['opening_odds'];
                            }

                            $odd->update([
                                'win_home'      => $indexes_odds['home'],
                                'win_guest'     => $indexes_odds['away'],
                                'draw'          => $indexes_odds['draw'],
                                'last_update'   => $match['markets_last_updated'],
                            ]);
                        }
                        continue;
                    }

                    $inserts[] = array_merge($insert_or_update , ['created_at' => $cr_date , 'updated_at' => $cr_date]);

                    $provider_ids[] = $match['id'];
                    if($odds){
                        foreach($odds as $o_key => $odd){
                            $indexes_odds = [];
                            foreach ($odd['outcomes'] as $out_k => $outcome){
                                $indexes_odds[$outcome['type']] = $outcome['opening_odds'];
                            }

                            $ods_inserts[$match['id']][] = [
                                'site_slug'     => \Str::slug($odd['name']),
                                'site_nickname' => $odd['name'],
                                'win_home'      => $indexes_odds['home'],
                                'win_guest'     => $indexes_odds['away'],
                                'draw'          => $indexes_odds['draw'],
                                'last_update'   => $match['markets_last_updated'],
                                'created_at'    => $cr_date,
                                'updated_at'    => $cr_date
                            ];
                        }
                    }
                }
                \Log::info('Before Insert' . $date);
                if($inserts){
                    if(Game::insert($inserts)){
                        $insert_ids = Game::whereIn('provider_slug' ,$provider_ids )->get(['id' , 'provider_slug'])->toArray();
                        $insert_odds = [];
                        foreach ($ods_inserts as $o_key => $odd){
                            foreach ($insert_ids as $i_key => $insert_data){
                                if($insert_data['provider_slug'] === $o_key) break;
                            }
                            if(isset($insert_data)){
                                foreach ($odd as $odd_key => $odd_insert){
                                    $insert_odds[] = array_merge(['match_id' => $insert_data['id']] , $odd_insert);
                                }
                            }
                        }
                        Odd::insert($insert_odds);
                    }else{
                        if($date === '2021-05-07'){
                            \Log::info('Insert Chexav');
                        }
                    }
                }else{
                    if($date === '2021-05-07'){
                        \Log::info('Insert Chkar');
                    }
                }
            }
        }
    }

    public function insertMatchesBySportSlug(){
//
    }

    public function getBookmakers($get_all = []){
        $types_response = [];

        $select = ['*'];

        if(isset($get_all['db']['select']) && $get_all['db']['select'] && is_array($get_all['db']['select'])){
            $select = $get_all['db']['select'];
        }

        if ((isset($get_all['api']) && $get_all['api']) || !$get_all) {
            $bookmakers = $this->withApiToken('books.json');
            if(isset($bookmakers['books'])){
                $types_response['api'] = $bookmakers['books'];
            }else{
                $types_response['api'] = false;
            }
        }

        if ((isset($get_all['db']) && $get_all['db']) || !$get_all) {
            if(isset($get_all['db']['wheres']) && $get_all['db']['wheres']){
                $wheres = $get_all['db']['wheres'];
            }
            if(isset($wheres)){
                $types_response['db'] = Bookmakers::where($wheres)->select($select)->get();
            }else{
                $types_response['db'] = Bookmakers::select($select)->get();
            }
        }

        if ($get_all) {
            return $types_response['db'] ?? $types_response['api'];
        }

        return $types_response;
    }

    public function insertBookmakers(){
        $bookmakers = $this->getBookmakers();
        if($bookmakers['api']){
            $inserts = [];
            $cr_date = date('Y-m-d H:i:s');
            foreach ($bookmakers['api'] as $b_key => $bookmaker){
                $insert_or_update = [
                    'name' => $bookmaker['name'],
                    'provider_slug' => $bookmaker['id'],
                ];

                if($ex_b = $bookmakers['db']->where('provider_slug' , $bookmaker['id'])->first()){
                    $diff = array_diff($ex_b->toArray() , $bookmaker);
                    if(isset($diff['name']) || isset($diff['id']) ){
                        $ex_b->update($insert_or_update);
                    }
                    continue;
                }
                $inserts[] = array_merge($insert_or_update , ['created_at' => $cr_date , 'updated_at' => $cr_date]);

            }

            if($inserts){
                Bookmakers::insert($inserts);
            }
        }
    }

}
