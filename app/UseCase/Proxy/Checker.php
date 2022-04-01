<?php

namespace App\UseCase\Proxy;

use App\Models\City;
use App\Models\Proxy;
use App\Models\ProxyAdditional;
use App\UseCase\Search\Params;
use App\UseCase\Search\SearchFactory;
use Illuminate\Support\Carbon;

class Checker
{
    public function __construct(private Proxy $proxy)
    {
    }

    public function check(string $source)
    {
        $proxyForChecker = $this->proxy->forChecker($source);
        if (empty($proxyForChecker) || !is_array($proxyForChecker)) {
            return;
        }

        $searchSource = SearchFactory::makeSearchBySourceName($source);
        $params = new Params();
        $params->setAdults(2);

        /* @var City $city */
        $city = City::query()->where('code', 'ROV')->first();
        $params->setCity($city);
        $params->setCheckInDate(Carbon::now()->addWeek());
        $params->setCheckOutDate(Carbon::now()->addWeeks(2));

        $searchSource->setParams($params);

        /* @var Proxy $proxyEntity */
        foreach ($proxyForChecker as $proxyEntity) {
            $proxyAdditional = $proxyEntity->proxyAdditional()->get()->first();
            if(!$proxyAdditional){
                $proxyAdditional = new ProxyAdditional();
                $proxyAdditional->proxy()->associate($proxyEntity);
            }
            try {
                $searchSource->search($proxyEntity);
                $proxyAdditional->{$source} = '1';
            } catch (\Exception $e) {
                $proxyAdditional->{$source} = '0';
            }
            $proxyAdditional->save();
        }
    }
}
