<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;


use App\Services\OddService;

// use App\Se

class RefreshExistsCategories implements ShouldQueue
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
        $odd_service = new OddService(config('services.odd'));
        $all_types =  $odd_service->updateSportTypes();
        if ($all_types['success']) {
            \Log::info('M<olodes');
        } else {
            \Log::info('Cron Job RefreshExists has error');
            if (isset($all_types['message'])) {
                \Log::info($all_types['message']);
            }
        }
    }
}
