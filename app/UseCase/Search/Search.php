<?php

namespace App\UseCase\Search;

use App\Http\Requests\SearchRequest;
use App\Jobs\SearchOstrovok;
use App\Jobs\SearchYandex;
use App\Models\City;
use App\Models\Proxy;
use GuzzleHttp\Client;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ResponseInterface;

class Search
{
    private ?string $source = null;

    private ?Proxy $lastProxy = null;

    public function __construct(private Proxy $proxy, private Sorter $sorter, private Client $client)
    {
    }

    public function search(SearchRequest $request): array
    {
        return $this->searchByParams($this->prepareParams($request));
    }

    public function searchByParams(Params $params): array
    {
        SearchYandex::dispatch($params)->onQueue('search_yandex');
        SearchOstrovok::dispatch($params)->onQueue('search_ostrovok');

        return [];
        /*$searchSources = config('search_sources');

        $requestArr = [];
        foreach ($searchSources as $source) {
            $searchSource = SearchFactory::makeSearchBySourceName($source);
            $searchSource->setParams($params);

            $sourceProxyList = $this->proxy->getAllEnabledBySource($source);
            foreach ($sourceProxyList as $proxyModel) {
                $requestArr[$source . '_' . $proxyModel->address] = $this->client->getAsync(
                    '',
                    $searchSource->getOptions($proxyModel)
                );
            }
        }

        if (empty($requestArr)) {
            throw new \Exception('Не удалось сформировать запрос для поиска');
        }

        $responses = \GuzzleHttp\Promise\settle($requestArr)->wait();

//        $searchResults =

        foreach ($responses as $key => $item) {
            list($source, $proxyAddress) = explode('_', $key);
            $searchSource = SearchFactory::makeSearchBySourceName($source);

            if ($this->isValidResponse($item, $searchSource)) {

            } else {

            }
        }
        unset($responses);*/

        /*$searchResults = collect([]);

        $searchSources = config('search_sources');
        if (empty($searchSources)) {
            throw new \Exception('Не заданы источники поиска');
        }

        foreach ($searchSources as $source) {
            $this->source = $source;
            $sourceEngine = \App::get($source);
            if (empty($sourceEngine) || (!$sourceEngine instanceof SearchSourceInterface)) {
                throw new \Exception('Не удалось создать поисковой движок с кодом ' . $source);
            }

            if (!Schema::hasColumn('proxies', $source)) {
                throw new \Exception('Не заданы прокси для источника ' . $source);
            }

            $this->proxy = $this->proxy->getRandBySource($source);

            if (!($this->lastProxy = $this->proxy->getRandBySource($source))) {
                throw new \Exception('Не найден прокси для источника ' . $source);
            }

            $sourceEngine->setParams($params);
            $results = $sourceEngine->search($this->lastProxy);
            if (!empty($results)) {
                $searchResults = $searchResults->merge($results);
            }
        }

        return $this->sorter->sort($searchResults);*/
    }

    public function getLastProxy(): ?Proxy
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
            Log::debug('Горемычный прокси: ' . $proxy->address);
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

    private function isValidResponse(array $response, SearchSourceInterface $searchSource): bool
    {
        return (
            isset($response['value'])
            && $response['value'] instanceof ResponseInterface
            && $response['value']->getStatusCode() === 200
            && $searchSource->isValidResponse($response['value'])
        );
    }
}
