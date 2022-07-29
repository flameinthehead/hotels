<?php

namespace App\UseCase\Ostrovok;

use App\Exceptions\OstrovokSearchException;
use App\Models\OstrovokCity;
use App\Models\SearchRequest;
use App\UseCase\Search\Params;
use App\UseCase\Search\SearchSourceInterface;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ResponseInterface;

class Search implements SearchSourceInterface
{
    public const SEARCH_BASE_URL = 'https://ostrovok.ru/hotel/search/v2/site/serp';

    public const CONNECTION_TIMEOUT = 5;
    public const TIMEOUT = 20;

    private \App\UseCase\Ostrovok\Params $params;

    public function __construct(private Client $client, private Suggestions $suggestions)
    {
    }

    public function search(array $proxyList, SearchRequest $searchRequest): void
    {
        if (empty($this->params)) {
            throw new \Exception('Не заданы параметры поиска');
        }

        if (!empty($searchRequest->telegramRequest->stars) && $searchRequest->telegramRequest->stars > 0) {
            $this->params->setFilterStars($searchRequest->telegramRequest->stars);
        }

        $requestArr = [];
        $options = $this->getOptions();
        foreach ($proxyList as $proxyModel) {
            $options[RequestOptions::PROXY] = $proxyModel->address;

            $requestArr[$proxyModel->address] = $this->client->getAsync(
                self::SEARCH_BASE_URL,
                $options
            );
        }

        $responses = \GuzzleHttp\Promise\settle($requestArr)->wait();


        $content = null;
        foreach ($responses as $responseItem) {
            if (!isset($responseItem['value']) || !($responseItem['value'] instanceof ResponseInterface)) {
                continue;
            }
            $content = json_decode($responseItem['value']->getBody()->getContents(), true);
            if (!empty($content) && is_array($content) && $this->isValidResponse($content)) {
                break;
            }
        }

        if (empty($content)) {
            throw new OstrovokSearchException('Ostrovok search invalid response');
        }

        foreach ($content['hotels'] as $row) {
            $oneResult = ResultFactory::makeResult($row, $this->params);
            if (!empty($oneResult)) {
                $oneResult->search_request_id = $searchRequest->id;
                $oneResult->save();
            }
        }
    }

    public function setParams(Params $generalParams)
    {
        if (empty($generalParams->getCity()->ostrovokCity()->first())) {
            $cityId = $this->suggestions->findByCityName($generalParams->getCity()->name);
            $ostrovokCity = new OstrovokCity();
            $ostrovokCity->ostrovok_city_id = $cityId;
            $ostrovokCity->city_id = $generalParams->getCity()->id;
            $ostrovokCity->save();
        }
        $this->params = \App\UseCase\Ostrovok\Params::makeSourceParams($generalParams);
    }

    public function getUrl(): string
    {
        return self::SEARCH_BASE_URL;
    }

    public function getOptions(): array
    {
        $json = [
            'session_params' => [
                'currency' => 'RUB',
                'language' => 'ru',
                'arrival_date' => $this->params->getArrivalDate(),
                'departure_date' => $this->params->getDepartureDate(),
                'region_id' => $this->params->getRegionId(),
                'travel_policies' => [
                    'rooms' => [],
                ],
                'paxes' => [
                    [
                        'adults' => $this->params->getAdults(),
                    ],
                ],
            ],
            'page' => 1,
            'map_hotels' => true,
            'sort' => 'price_asc',
            'filters' => $this->params->getFilter()
        ];

        if (empty($json['filters'])) {
            unset($json['filters']);
        }

        return [
            RequestOptions::JSON => json_decode(json_encode($json)),
            RequestOptions::CONNECT_TIMEOUT => self::CONNECTION_TIMEOUT,
            RequestOptions::TIMEOUT => self::TIMEOUT,
        ];
    }

    public function isValidResponse(array $content): bool
    {
        return (!empty($content) && is_array($content) && isset($content['hotels']));
    }
}
