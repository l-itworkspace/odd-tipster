<?php

namespace App\Http\Controllers;

use App\Models\SportTypes;
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


    public function home()
    {
        $sport_types = $this->odd_service->getSportTypes(['db' => true]);

        return view('welcome', compact(['sport_types']));
    }
}
