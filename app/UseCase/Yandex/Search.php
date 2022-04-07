<?php

namespace App\UseCase\Yandex;

use App\Exceptions\YandexSearchException;
use App\Models\Proxy;
use App\UseCase\Search\SearchSourceInterface;
use GuzzleHttp\Client;
use GuzzleHttp\TransferStats;
use Illuminate\Support\Collection;
use Symfony\Component\Serializer\Serializer;

class Search implements SearchSourceInterface
{
    public const SEARCH_BASE_URL = 'https://travel.yandex.ru/api/hotels/searchHotels';

    private Params $params;

    public function __construct(private Client $client, private Serializer $serializer)
    {
    }

    public function search(Proxy $proxy): Collection
    {
        if (empty($this->params)) {
            throw new \Exception('Не заданы параметры поиска');
        }
        $options = [
            'query' => $this->serializer->normalize($this->params, 'array'),
            'headers' => [
                "Accept" => "text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
                "Accept-Encoding" => "gzip, deflate, br",
                "Accept-Language" => "ru-RU,ru;q=0.9",
                "Connection" => "keep-alive",
                "Host" => "travel.yandex.ru",
                "sec-ch-ua" => '" Not A;Brand";v="99", "Chromium";v="99"',
                "sec-ch-ua-mobile" => "?0",
                "sec-ch-ua-platform" => '"Linux"',
                "Sec-Fetch-Dest" => "document",
                "Sec-Fetch-Mode" => "navigate",
                "Sec-Fetch-Site" => "none",
                "Sec-Fetch-User" => "?1",
                "Upgrade-Insecure-Requests" => "1",
                "User-Agent" => "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/99.0.4844.84 Safari/537.36",
            ],
            'connect_timeout' => 5,
            'on_stats' => function(TransferStats $stats) use ($proxy) {
                echo 'transfer time = ' . $stats->getTransferTime()."\r\n";
            }
        ];
        if(!empty($proxy) && !empty($proxy->address)){
            $options['proxy'] = $proxy->address;
        }

        $response = $this->client->request('GET', self::SEARCH_BASE_URL, $options);
        if ($response->getStatusCode() != 200) {
            throw new YandexSearchException('Yandex search response code != 200');
        }

        $response = json_decode($response->getBody()->getContents(), true);
        if (empty($response) || !is_array($response)) {
            throw new YandexSearchException('Yandex search invalid response');
        }

        if (!isset($response['data']['hotels']) || !is_array($response['data']['hotels'])) {
            throw new YandexSearchException('Yandex search no hotels found');
        }

        $searchResults = collect([]);
        foreach ($response['data']['hotels'] as $row) {
            $searchResults->push(ResultFactory::makeResult($row, $this->params));
        }

        return $searchResults;
    }

    public function setParams(\App\UseCase\Search\Params $generalParams)
    {
        $this->params = Params::makeSourceParams($generalParams);
    }
}
