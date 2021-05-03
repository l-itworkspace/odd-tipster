<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Jobs\RefreshExistsCategories as JobRefreshExistsCategories;

class RefreshExistsCategories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'refresh:categories';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh Categories , change actives if in api is not active.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        JobRefreshExistsCategories::dispatch();
    }
}
