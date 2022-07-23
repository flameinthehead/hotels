<?php

namespace App\UseCase\Yandex;

use App\Exceptions\YandexSearchException;
use App\Models\Proxy;
use App\Models\YandexCity;
use App\UseCase\Search\SearchSourceInterface;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Serializer\Serializer;

class Search implements SearchSourceInterface
{
    public const SEARCH_BASE_URL = 'https://travel.yandex.ru/api/hotels/searchHotels';

    public const CONNECTION_TIMEOUT = 5;

    private Params $params;

    public function __construct(private Client $client, private Serializer $serializer, private Suggestions $suggestions)
    {
    }

    public function search(array $proxyList): Collection
    {
        if (empty($this->params)) {
            throw new \Exception('Не заданы параметры поиска');
        }

        $options = $this->getOptions();
        $response = $this->getResponse($options, $proxyList);

        $hotels = [];

        for($i = 0; $i < 3; ++$i) {
            $hotels = array_merge($hotels, $response['data']['hotels']);
            if ($response['data']['offerSearchProgress']['finished'] === true) {
                break;
            }
            sleep(1);
            $options['query']['pollIteration'] += 1;
            $options['query']['context'] = $response['data']['context'];

            $response = $this->getResponse($options, $proxyList);
        }

        $searchResults = collect([]);
        foreach ($hotels as $row) {
            $oneResult = ResultFactory::makeResult($row, $this->params);
            if (!empty($oneResult)) {
                $oneResult->save();
                $searchResults->push($oneResult);
            }
        }

        Log::debug(json_encode($searchResults));

        return $searchResults;
    }

    public function setParams(\App\UseCase\Search\Params $generalParams)
    {
        if (empty($generalParams->getCity()->yandexCity()->first())) {
            $geoId = $this->suggestions->findByCityName($generalParams->getCity()->name);
            $yandexCity = new YandexCity();
            $yandexCity->yandex_city_id = $geoId;
            $yandexCity->city_id = $generalParams->getCity()->id;
            $yandexCity->save();
        }

        $this->params = Params::makeSourceParams($generalParams);
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
                "Accept" => "text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
                "Accept-Encoding" => "gzip, deflate, br",
                "Accept-Language" => "ru-RU,ru;q=0.9",
                "Connection" => "keep-alive",
                "Host" => "travel.yandex.ru",
                "sec-ch-ua" => '" Not A;Brand";v="99", "Chromium";v="100"',
                "sec-ch-ua-mobile" => "?0",
                "sec-ch-ua-platform" => '"Linux"',
                "Sec-Fetch-Dest" => "document",
                "Sec-Fetch-Mode" => "navigate",
                "Sec-Fetch-Site" => "none",
                "Sec-Fetch-User" => "?1",
                "Upgrade-Insecure-Requests" => "1",
                "User-Agent" => "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/100.0.4896.127 Safari/537.36",
            ],
            RequestOptions::CONNECT_TIMEOUT => self::CONNECTION_TIMEOUT,
        ];
    }

    public function isValidResponse(array $content): bool
    {
        return (!empty($content) && is_array($content) && isset($content['data']['hotels']));
    }

    /**
     * @param array $options
     * @param Proxy[] $proxyList
     * @return array
     * @throws YandexSearchException
     */
    private function getResponse(array $options, array $proxyList): array
    {
        $requestArr = [];
        foreach ($proxyList as $proxyModel) {
            $options[RequestOptions::PROXY] = $proxyModel->address;

            $requestArr[$proxyModel->address] = $this->client->getAsync(
                self::SEARCH_BASE_URL,
                $options
            );
        }

        $responses = \GuzzleHttp\Promise\settle($requestArr)->wait();

        foreach ($responses as $responseItem) {
            if (!isset($responseItem['value']) || !($responseItem['value'] instanceof ResponseInterface)) {
                continue;
            }
            $content = json_decode($responseItem['value']->getBody()->getContents(), true);
            if ($this->isValidResponse($content)) {
                return $content;
            }
        }

        throw new YandexSearchException('Yandex search invalid response');
    }
}
