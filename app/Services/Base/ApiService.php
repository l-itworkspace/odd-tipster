<?php

namespace App\Services\Base;

use Illuminate\Support\Facades\Http;

class ApiService
{

    protected $credentials;

    /**
     *  Method will be send request by url
     *
     * @param string $method (get|post|put|delete)
     * @param string $url This will be append to your credentials url
     * @param array|string $data
     * @param boolean $json If resault will be convert to json
     *
     * @return Http response
     *
     * @throw Exception
     *
     */

    public function requestTo(string $method, string $url, $data = [], bool $json = false)
    {

        if (!$this->credentials) throw new Exception('Protected credentials will be initialized');

        if (!isset($this->credentials['api_path'])) throw new Exception('api_path Key is important');

        if (!in_array($method, ['get', 'post', 'put', 'delete'])) throw new Exception('$method will be (get|post|put|delete)');

        $url_data = [
            $this->credentials['api_path'],
            $url
        ];

        $url = concatForUrls($url_data);

        $res = Http::$method($url, $data);

        if ($json) {
            return $res->json();
        }

        return $res->body();
    }
}
