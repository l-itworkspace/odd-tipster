<?php

namespace App\Jobs;

use App\Models\SportTypes;
use App\Models\Tournament;
use App\Services\SportTraderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class InsertNotExistsTournaments implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $matches , $cats;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($matches , $exists_cats)
    {
        $this->matches   = $matches;
        $this->cats    = $exists_cats;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $time = microtime(true);
        $cr_date = date('Y-m-d H:i:s');
        $insert_parents = [];
        $sport_service = new SportTraderService(config('services.sport_traders'));

        $tournaments = $sport_service->getTournaments([
            'db' => [
                'select' => ['id','provider_slug'],
                'without_rel' => true
            ]
        ]);
        foreach($this->matches as $k => $match){
            $type_sport = $this->cats->where('provider_slug' , $match['tournament']['sport']['id'])->first();
            $category = $this->cats->where('provider_slug' , $match['tournament']['category']['id'])->first();

            if( !$type_sport && arraySearch($match['tournament']['sport']['id'], $insert_parents['sport_types'] , 'provider_slug') === false){
                    $insert_parents['sport_types'][] = [
                        'name'          => $this->match['tournament']['sport']['name'],
                        'provider_slug' => $this->match['tournament']['sport']['id'],
                        'slug'          => slugify($this->match['tournament']['sport']['name']),
                        'created_at'    => $cr_date,
                        'updated_at'    => $cr_date
                    ];
            }

            if(!$category){
                $insert_cat = false;
                if(isset($insert_parents['categories']) ){
                    if(arraySearch($match['tournament']['category']['id'] , $insert_parents['categories'] , 'provider_slug') === false){
                        $insert_cat = true;
                    }
                }else{
                    $insert_cat = true;
                }

                if($insert_cat){
                    $parent_id = $type_sport->id ?? SportTypes::where('provider_slug' , $match['tournament']['sport']['id'])->first()->id ?? $match['tournament']['sport']['id'];

                    $insert_parents['categories'][] = [
                        'parent_id' => $parent_id,
                        'name'      =>  $match['tournament']['category']['name'],
                        'slug'      => slugify($match['tournament']['category']['name']),
                        'provider_slug' =>  $match['tournament']['category']['id'],
                        'type'         => SportTypes::TYPE_CATEGORY,
                        'created_at'   => $cr_date,
                        'updated_at'   => $cr_date
                    ];
                }
            }
            $tournament = $tournaments->where('provider_slug' , $match['tournament']['id'])->first();
            if(!$tournament){
                $insert_t = false;
                if(isset($insert_parents['tournaments'])){
                    if(arraySearch($match['tournament']['id'] , $insert_parents['tournaments'] , 'provider_slug')  === false){
                        $insert_t = true;
                    }
                }else{
                    $insert_t = true;
                }
                if($insert_t){
                    $insert_parents['tournaments'][] = [
                        'name' => $match['tournament']['name'],
                        'provider_slug' => $match['tournament']['id'],
                        'category_id'   => SportTypes::where('provider_slug' , $match['tournament']['category']['id'])->first()->id ?? $match['tournament']['category']['id'],
                        'created_at'   => $cr_date,
                        'updated_at'   => $cr_date
                    ];
                }
            }
        }

        if(isset($insert_parents['sport_types']) && $insert_parents['sport_types']){
            SportTypes::insert($insert_parents['sport_types']);
        }

        $sport_types_select = [
            'db' => [
                'wheres' => [
                    ['active' ,'=',1],
                ],
                'select' => ['id' , 'provider_slug' , 'type']
            ]
        ];

        if(isset($insert_parents['categories']) && $insert_parents['categories']){
            $sport_types = $sport_service->getSportTypes($sport_types_select);
            foreach ($insert_parents['categories'] as $k => $category){
                if(is_numeric($category['parent_id'])){
                    continue;
                }
                if($parent = $sport_types->where('provider_slug' , $category['parent_id'])->first()){
                    $insert_parents['categories'][$k]['parent_id'] =$parent->id;
                }
            }
            SportTypes::insert($insert_parents['categories']);
        }

        if(isset($insert_parents['tournaments']) && $insert_parents['tournaments']){
            $sport_types = $sport_service->getSportTypes($sport_types_select);
            foreach ($insert_parents['tournaments'] as $k => $tournament){
                if(is_numeric($tournament['category_id'])) continue;

                $insert_parents['tournaments'][$k]['category_id'] = $sport_types->where('provider_slug', $tournament['category_id'])->first()->id;
            }
            foreach (array_chunk($insert_parents['tournaments'] , 500) as $k => $value){
               Tournament::insert($value);
            }
        }
        InsertMatches::dispatch($this->matches , $sport_service);
    }
}
