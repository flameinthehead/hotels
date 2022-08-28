<?php

namespace App\UseCase\Sutochno;

use App\Exceptions\SutochnoSearchException;
use App\Models\SearchRequest;
use App\Models\SutochnoCity;
use App\UseCase\Search\SearchSourceInterface;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Serializer\Serializer;

class Search implements SearchSourceInterface
{
    private const SEARCH_BASE_URL = 'https://sutochno.ru/api/json/search/searchObjects?';
    private const CONNECTION_TIMEOUT = 5;
    private const TIMEOUT = 10;

    private Params $params;

    public function __construct(
        private Client $client,
        private Serializer $serializer,
        private Suggestions $suggestions,
        private BookUrlEncoder $bookUrlEncoder
    ) {
    }

    public function search(array $proxyList, SearchRequest $searchRequest): void
    {
        $requestArr = [];
        $options = $this->getOptions();
        foreach ($proxyList as $proxyModel) {
            $options[RequestOptions::PROXY] = $proxyModel->address;

            $requestArr[$proxyModel->address] = $this->client->getAsync(
                self::SEARCH_BASE_URL,
                $options
            );
        }
        unset($proxyList);

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
            throw new SutochnoSearchException('Sutochno search invalid response, id = ' . $searchRequest->id);
        }

        foreach ($content['data']['objects'] as $row) {
            $oneResult = ResultFactory::makeResult($row, $this->params, $this->bookUrlEncoder);
            if (!empty($oneResult)) {
                $oneResult->search_request_id = $searchRequest->id;
                $oneResult->save();
            }
        }
    }

    public function setParams(\App\UseCase\Search\Params $generalParams)
    {
        if (empty($generalParams->getCity()->sutochnoCity()->first())) {
            $cityData = $this->suggestions->findByCityName($generalParams->getCity()->name);
            $sutochnoCity = new SutochnoCity();
            $sutochnoCity->sutochno_city_data = $cityData;
            $sutochnoCity->city_id = $generalParams->getCity()->city_id;
            $sutochnoCity->save();
        }

        $this->params = \App\UseCase\Sutochno\Params::makeSourceParams($generalParams);
    }

    public function getUrl(): string
    {
        return self::SEARCH_BASE_URL;
    }

    public function getOptions(): array
    {
        return [
            RequestOptions::QUERY => $this->serializer->normalize($this->params, 'array'),
            RequestOptions::HEADERS => [
                'token' => 'Hy6U3z61fflbgT2yJ/VdlQ2719',
            ],
            RequestOptions::CONNECT_TIMEOUT => $this->getConnectionTimeout(),
            RequestOptions::TIMEOUT => $this->getTimeout(),
        ];
    }

    public function isValidResponse(array $content): bool
    {
        return (!empty($content) && isset($content['data']['objects']));
    }

    public function getConnectionTimeout(): int
    {
        return self::CONNECTION_TIMEOUT;
    }

    public function getTimeout(): int
    {
        return self::TIMEOUT;
    }
}
