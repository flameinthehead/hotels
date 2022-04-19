<?php

namespace App\UseCase\Ostrovok;

use App\Exceptions\OstrovokSearchException;
use App\Models\Proxy;
use App\UseCase\Search\Params;
use App\UseCase\Search\SearchSourceInterface;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;

class Search implements SearchSourceInterface
{
    public const SEARCH_BASE_URL = 'https://ostrovok.ru/hotel/search/v2/site/serp';

    private \App\UseCase\Ostrovok\Params $params;

    public function __construct(private Client $client)
    {
    }

    public function search(Proxy $proxy): Collection
    {
        if (empty($this->params)) {
            throw new \Exception('Не заданы параметры поиска');
        }

        $response = $this->client->post(
            self::SEARCH_BASE_URL,
            [
                'json' => json_decode(json_encode($this->prepareOptions())),
                'proxy' => $proxy->address,
            ]
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
            $searchResults->push(ResultFactory::makeResult($row, $this->params));
        }

        return $searchResults;
    }

    public function setParams(Params $generalParams)
    {
        $this->params = \App\UseCase\Ostrovok\Params::makeSourceParams($generalParams);
    }

    private function prepareOptions(): array
    {
        return [
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
    }
}
