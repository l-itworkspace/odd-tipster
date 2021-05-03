<?php

namespace App\Services;

use App\Services\Base\ApiService;


use App\Models\SportTypes;

class OddService extends ApiService
{

    public function __construct($credentials)
    {
        $this->credentials = $credentials;
    }

    public function getSportTypes($get_all = [])
    {
        $types_response = [];

        if ((isset($get_all['api']) && $get_all['api']) || !$get_all) {
            $types_response['api'] = $this->withApiToken('get', 'sports');
        }

        if ((isset($get_all['db']) && $get_all['db']) || !$get_all) {
            $types_response['db'] = SportTypes::where('active', 1)->get(['name', 'group', 'details', 'type', 'active', 'parent_id']);
        }

        if ($get_all) {
            return $types_response['db'] ?? $types_response['api'];
        }

        return $types_response;
    }

    public function getLinkes()
    {
        // return $this->requestTo('get', '', ], true);
    }

    public function updateSportTypes()
    {
        $sport_types        = $this->getSportTypes();
        $exists = $sport_types['db']->toArray();
        $insert_into = [];
        $cr_date = date('Y-m-d H:i:s');

        foreach ($sport_types['api']['data'] as $k => $type_api) {

            foreach ($exists as $key => $ex_types) {
                if ($ex_types['type'] === $type_api['key']) break;
            }

            $insert_or_update = [
                'type'    => $type_api['key'],
                'details' => $type_api['details'],
                'name'    => $type_api['title'],
                'group'   => $type_api['group'],
                'active'  => $type_api['active']
            ];

            if (isset($ex_types)) {
                try {
                    SportTypes::where('type', $type_api['key'])->update($insert_or_update);
                } catch (\Exception $e) {
                    return ['success' => false, 'message' => $e->getMessage()];
                }
                continue;
            }

            $insert_into[] = array_merge($insert_or_update, ['created_at' => $cr_date, 'updated_at' => $cr_date]);
        }

        if ($insert_into) {
            return ['success' => SportTypes::insert($insert_into)];
        }
    }

    public function getOdds()
    {
        return $this->withApiToken('get', 'odds', ['sport' => 'baseball_mlb', 'region' => 'us']);
    }

    public function withApiToken($method, $url, $data = [])
    {
        return $this->requestTo($method, $url, array_merge($data, ['apiKey' => $this->credentials['key']]), true);
    }
}
