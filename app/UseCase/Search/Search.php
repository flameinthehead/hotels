<?php

namespace App\UseCase\Search;

use App\Http\Requests\SearchRequest;
use App\Models\City;
use App\Models\Proxy;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;


class Search
{
    public function __construct(private Proxy $proxy)
    {
    }

    public function search(SearchRequest $request)
    {
        $searchSources = config('search_sources');
        if (empty($searchSources)) {
            throw new \Exception('Не заданы источники поиска');
        }

        $params = $this->prepareParams($request);

        $searchResults = collect([]);

        foreach ($searchSources as $source) {
            /* @var SearchSourceInterface $sourceEngine */
            $sourceEngine = \App::get($source);
            if (empty($sourceEngine) || (!$sourceEngine instanceof SearchSourceInterface)) {
                throw new \Exception('Не удалось создать поисковой движок с кодом ' . $source);
            }

            if (!Schema::hasColumn('proxies', $source)) {
                throw new \Exception('Не заданы прокси для источника ' . $source);
            }
            /* @var Proxy $sourceProxy */
            $sourceProxy = $this->proxy->getRandBySource($source);
            if(!$sourceProxy){
                throw new \Exception('Не найден прокси для источника ' . $source);
            }

            $sourceEngine->setParams($params);
            $results = $sourceEngine->search($this->proxy);
            if (!empty($results)) {
                $searchResults = $searchResults->merge($results);
            }
        }

        dd($searchResults);
    }

    private function prepareParams(SearchRequest $request): Params
    {
        $cityCode = $request->get('city');
        /* @var City $city */
        $city = City::query()->where('code', $cityCode)->first();
        if (!$city) {
            throw new \Exception('Неизвестный город с кодом ' . $cityCode);
        }

        $params = new Params();
        $params->setCity($city);
        $params->setCheckInDate(Carbon::make($request->get('checkIn')));
        $params->setCheckOutDate(Carbon::make($request->get('checkOut')));
        $params->setAdults($request->get('adults'));

        return $params;
    }
}
