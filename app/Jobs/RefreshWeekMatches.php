<?php

namespace App\Jobs;

use App\Services\SportTraderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RefreshWeekMatches implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
       $odd_service = new SportTraderService(config('services.sport_traders'));
       $date = date('Y-m-d' , strtotime('next monday'));
       $next_date = date('Y-m-d');
       while($next_date != $date){
           $odd_service->insertMatchesBefore($next_date);
           $next_date = date('Y-m-d' , (strtotime($next_date) + 86400));
       }
    }
}
