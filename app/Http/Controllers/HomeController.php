<?php

namespace App\Http\Controllers;


use App\Services\SportTraderService;
use Illuminate\Http\Request;
use App\Http\Requests\GetOddsRequest;


// Services
use App\Services\PinnacleService;


class HomeController extends Controller
{

    public $odd_service;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth')->only('index');
        $this->odd_service = new SportTraderService(config('services.sport_traders'));
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }


    public function home(Request $req)
    {
        $date = $req->date ?: date('Y-m-d');
        $selects = [
            'sport_types' => [
                'db' =>[
                    'whereHas' => 'categories.gamesToday',
                    'with' => ['categories' => function ($q){
                        $q->has('gamesToday');
                    }],
                    'where' => [
                        ['parent_id'  , '=' , 0 ]
                    ]
                ]
            ],
            'tournaments' => [
                'db' => [
                    'whereHas' => 'gamesToday',
                    'with'     => 'gamesToday'
                ]
            ]
        ];

        $sport_types = $this->odd_service->getSportTypes($selects['sport_types']);
        if($req->has('update-full')){
//            $this->odd_service->insertSportTypes();
            $this->odd_service->insertBookmakers();
        }

        if($req->cat_id){
            $selects['tournaments']['db']['wheres'] = [
                ['category_id' , '=' , $req->cat_id]
            ];
        }

        $tournaments = $this->odd_service->getTournaments($selects['tournaments']);

        return view('welcome', compact(['sport_types' , 'tournaments']));
    }

    public function getOdds(GetOddsRequest $req){
        $matches = $this->odd_service->getOddsByMatchId($req->match_id , [['site_slug' , '<>' , $req->showed_site]]);

        return ['success' => true , 'data' => $matches];
    }

}
