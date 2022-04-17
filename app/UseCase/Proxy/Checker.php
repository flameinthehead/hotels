<?php

namespace App\UseCase\Proxy;

use App\Models\City;
use App\Models\Proxy;
use App\UseCase\Search\Params;
use App\UseCase\Search\SearchFactory;
use Illuminate\Support\Carbon;

class Checker
{
    public function __construct(private Proxy $proxy)
    {
    }

    public function check(string $searchSourceCode, string $proxySource = null, \Illuminate\Console\OutputStyle $output)
    {
        $proxyForChecker = $this->proxy->forChecker($searchSourceCode, $proxySource);
        if (empty($proxyForChecker) || !is_array($proxyForChecker)) {
            return;
        }

        $searchSource = SearchFactory::makeSearchBySourceName($searchSourceCode);
        $params = new Params();
        $params->setAdults(2);

        /* @var City $city */
        $city = City::query()->where('code', 'ROV')->first();
        $params->setCity($city);
        $params->setCheckInDate(Carbon::now()->addWeek());
        $params->setCheckOutDate(Carbon::now()->addWeeks(2));

        $searchSource->setParams($params);

        $bar = $output->createProgressBar(count($proxyForChecker));
        /* @var Proxy $proxyEntity */
        foreach ($proxyForChecker as $proxyEntity) {
            $bar->advance();
            try {
                $searchSource->search($proxyEntity);
                $proxyEntity->{$searchSourceCode} = '1';
            } catch (\Exception $e) {
                $proxyEntity->{$searchSourceCode} = '0';
            }
            $proxyEntity->save();
        }
        $bar->finish();
    }
}
