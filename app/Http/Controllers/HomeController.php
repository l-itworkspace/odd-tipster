<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Http\Requests\GetOddsRequest;


// Services
use App\Services\OddService;




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
        $this->odd_service = new OddService(config('services.odd'));
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
        $sport_types = $this->odd_service->getSportTypes(['db' => true]);

        $select = [
            'db'=> [
                'with_odd' => true,
            ]
        ];

        $matches = $this->odd_service->getMatches($select , $req->sport_type);

        return view('welcome', compact(['sport_types' , 'matches']));
    }

    public function getOdds(GetOddsRequest $req){
        $matches = $this->odd_service->getOddsByMatchId($req->match_id , [['site_slug' , '<>' , $req->showed_site]]);

        return ['success' => true , 'data' => $matches];
    }
}
