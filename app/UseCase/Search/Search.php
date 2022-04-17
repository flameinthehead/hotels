<?php

namespace App\UseCase\Search;

use App\Http\Requests\SearchRequest;
use App\Models\City;
use App\Models\Proxy;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Collection;

class Search
{
    private string $source;

    public function __construct(private Proxy $proxy, private Proxy $lastProxy)
    {
    }

    public function search(SearchRequest $request): Collection
    {
        $searchSources = config('search_sources');
        if (empty($searchSources)) {
            throw new \Exception('Не заданы источники поиска');
        }

        $params = $this->prepareParams($request);

        $searchResults = collect([]);

        foreach ($searchSources as $source) {
            $this->source = $source;
            /* @var SearchSourceInterface $sourceEngine */
            $sourceEngine = \App::get($source);
            if (empty($sourceEngine) || (!$sourceEngine instanceof SearchSourceInterface)) {
                throw new \Exception('Не удалось создать поисковой движок с кодом ' . $source);
            }

            if (!Schema::hasColumn('proxies', $source)) {
                throw new \Exception('Не заданы прокси для источника ' . $source);
            }
            /* @var Proxy */
            $this->lastProxy = $this->proxy->getRandBySource($source);

            if (! $this->lastProxy) {
                throw new \Exception('Не найден прокси для источника ' . $source);
            }

            $sourceEngine->setParams($params);
            $results = $sourceEngine->search($this->proxy);
            if (!empty($results)) {
                $searchResults = $searchResults->merge($results);
            }
        }

        return $searchResults;
    }

    public function getLastProxy(): Proxy
    {
        return $this->lastProxy;
    }

    public function getLastSource(): string
    {
        return $this->source;
    }

    public function disableLastProxy()
    {
        $this->getLastProxy()->{$this->getLastSource()} = '0';
        $this->getLastProxy()->save();
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
