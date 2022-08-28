<?php

namespace App\UseCase\Yandex;

use App\Models\Proxy;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;

class Suggestions
{
    public const BASE_URL_PATTERN = 'https://travel.yandex.ru/api/hotels/searchSuggest?query={query}&pathname=%2Fhotels%2F&limit=1&language=ru&domain=ru';
    public const TIMEOUT = 10;

    public function __construct(private Client $client, private Proxy $proxy)
    {
    }

    public function findByCityName(string $cityName): int
    {
        $proxyList = $this->proxy->getAllEnabledBySource('yandex');

        $requestArr = [];
        $options = [
            RequestOptions::TIMEOUT => self::TIMEOUT
        ];
        foreach ($proxyList as $proxyModel) {
            $options[RequestOptions::PROXY] = $proxyModel->address;

            $requestArr[$proxyModel->address] = $this->client->getAsync(
                str_replace('{query}', $cityName, self::BASE_URL_PATTERN),
                $options
            );
        }

        $responses = \GuzzleHttp\Promise\settle($requestArr)->wait();

        foreach ($responses as $responseItem) {
            if (!isset($responseItem['value']) || !($responseItem['value'] instanceof ResponseInterface)) {
                continue;
            }
            $decoded = json_decode($responseItem['value']->getBody()->getContents(), true);
            if (!isset($decoded['data']['items']) || !is_array($decoded['data']['items'])) {
                continue;
            }

            foreach ($decoded['data']['items'] as $cityData) {
                if ($cityData['name'] != $cityName || empty($cityData['redirectParams']['geoId'])) {
                    continue;
                }
                return $cityData['redirectParams']['geoId'];
            }
        }

        throw new \Exception('Yandex - Не корректный формат ответа от Suggestions');
    }
}
