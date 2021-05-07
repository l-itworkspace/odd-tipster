<?php

namespace App\Services\Base;

use Illuminate\Support\Facades\Http;

class ApiService
{

    protected $credentials,$with_auth,$headers;

    public function __construct($credentials)
    {
        $this->credentials = $credentials;
    }

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

        if (!$this->credentials) throw new \Exception('Protected credentials will be initialized');

        if (!isset($this->credentials['api_path'])) throw new \Exception('api_path Key is important');

        $method = strtolower($method);
        if (!in_array($method, ['get', 'post', 'put', 'delete'])) throw new \Exception('$method will be (get|post|put|delete)');

        $url_data = [
            $this->credentials['api_path'],
            $url
        ];

        $url = concatForUrls($url_data);

        if(isset($this->with_token) && $this->with_token){

            if($this->auth_type === 'basic'){
                if(!isset($this->credentials['login']) || !isset($this->credentials['password'])) throw new \Exception('Login Password required for Basic Auth');

                if(!$this->credentials['login'] || !$this->credentials['password'] ) throw new \Exception('Login Password required for ');

                if(isset($this->credentials['encode_type']) && $this->credentials['encode_type'] === 'base64'){
                    if(!isset($this->headers)) $this->headers = [];

                    $this->headers[] = ['Authorization' => 'Basic ' . base64_encode($this->credentials['login'] . ':' . $this->credentials['password'])];
                }
            }

        }

        if($this->headers){
            $res = Http::withHeaders($this->headers)->$method($url, $data);
        }else{
            $res = Http::$method($url, $data);
        }

        if ($json) {
            return $res->json();
        }

        return $res->body();
    }

    public function withApiToken($url, $method = 'get', $data = [])
    {
        $this->app_key_name = $this->app_key_name ?? 'apiKey';
        return $this->requestTo($method, $url, array_merge($data, [ $this->app_key_name => $this->credentials['api_key'] , 'limit' => 20]), true);
    }
}
