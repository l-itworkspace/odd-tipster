<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;


use App\Services\SportTraderService;


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
        $odd_service = new SportTraderService(config('services.sport_traders'));
        $all_types =  $odd_service->insertSportTypes(true);
        if (isset($all_types['success']) && $all_types['success']) {
            \Log::info('Molodes');
        } else {
            \Log::info('Cron Job RefreshExists has error');
            if (isset($all_types['message'])) {
                \Log::info($all_types['message']);
            }
        }
    }
}
