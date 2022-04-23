<?php

namespace App\UseCase\Search;

use App\Http\Requests\SearchRequest;
use App\Models\City;
use App\Models\Proxy;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class Search
{
    private ?string $source = null;

    public function __construct(private Proxy $proxy, private Proxy $lastProxy, private Sorter $sorter)
    {
    }

    public function search(SearchRequest $request): array
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

        return $this->sorter->sort($searchResults);
    }

    public function getLastProxy(): Proxy
    {
        return $this->lastProxy;
    }

    public function getLastSource(): ?string
    {
        return $this->source;
    }

    public function disableLastProxy(): void
    {
        $proxy = $this->getLastProxy();
        $source = $this->getLastSource();
        if (!empty($proxy) && !empty($source)) {
            $proxy->{$source} = '0';
            $proxy->save();
        }
    }

    private function prepareParams(SearchRequest $request): Params
    {
        $cityCode = $request->get('city');
        /* @var City $city */
        $city = City::query()->where('name', $cityCode)->first();
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
