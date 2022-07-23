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

    private \App\UseCase\Ostrovok\Params $params;

    public function __construct(private Client $client, private Suggestions $suggestions)
    {
    }

    public function search(array $proxyList, SearchRequest $searchRequest): Collection
    {
        if (empty($this->params)) {
            throw new \Exception('Не заданы параметры поиска');
        }

        $response = $this->client->post(
            self::SEARCH_BASE_URL,
            $this->getOptions()
        );

        if ($response->getStatusCode() != 200) {
            throw new OstrovokSearchException('Ostrovok search response code != 200');
        }


        $content = json_decode($response->getBody()->getContents(), true);

        if (empty($content) || !is_array($content) || !isset($content['hotels'])) {
            throw new OstrovokSearchException('Некорретный ответ от Ostrovok');
        }


        $searchResults = collect([]);
        foreach ($content['hotels'] as $row) {
            $oneResult = ResultFactory::makeResult($row, $this->params);
            if (!empty($oneResult)) {
                $searchResults->push($oneResult);
            }
        }

        return $searchResults;
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
            'sort' => 'price_asc'
        ];

        return [
            'json' => json_decode(json_encode($json)),
            'connect_timeout' => self::CONNECTION_TIMEOUT,
        ];
    }

    public function isValidResponse(array $content): bool
    {
        return (!empty($content) && is_array($content) && isset($content['hotels']));
    }
}
