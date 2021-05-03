<?php

namespace App\Services;

use App\Services\Base\ApiService;

class OddService extends ApiService{

    public function __construct($credentials)
    {
        $this->credentials = $credentials;
    }

}
