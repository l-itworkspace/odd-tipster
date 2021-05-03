<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
    public function __construct( )
    {
        $this->middleware('auth')->only('index');
        $this->odd_service = new OddService(\Config::get('services.odd'));
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


    public function home(){

//    https://fly.sportsdata.io/v3/soccer/scores/json/Areas?key=558d13afbe0b4c30af18c8254b4c8ae4
//    https://api.the-odds-api.com/v3/odds/?sport=soccer_epl&region=uk&apiKey=bb604b302db9e801ac7d4f30f43922cf
//        $response = Http::get('https://api.the-odds-api.com/v3/sports/?apiKey=bb604b302db9e801ac7d4f30f43922cf')->body();
//        dd($response);
          dd($this->odd_service->requestTo('get' , 'sports' , ['apiKey' => 'bb604b302db9e801ac7d4f30f43922cf']));
//        $response = Http::get('https://api.the-odds-api.com/v3/odds/?sport=soccer_epl&region=uk&apiKey=bb604b302db9e801ac7d4f30f43922cf')->json();

//        dd($response);
    }


}
