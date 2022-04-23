<?php

namespace App\UseCase\Yandex;

use GuzzleHttp\Client;

class Suggestions
{
    public const BASE_URL_PATTERN = 'https://travel.yandex.ru/api/hotels/searchSuggest?query={query}&pathname=%2Fhotels%2F&limit=1&language=ru&domain=ru';

    public function __construct(private Client $client)
    {
    }

    public function findByCityName(string $cityName): int
    {
        $result = $this->client->request(
            'GET',
            str_replace('{query}', $cityName,
                self::BASE_URL_PATTERN)
        );

        if ($result->getStatusCode() !== 200) {
            throw new \Exception('Не удалось получить ответ в Suggestions');
        }



        $content = $result->getBody()->getContents();

        $decoded = json_decode($content, true);
        if (!isset($decoded['data']['items']) || !is_array($decoded['data']['items'])) {
            throw new \Exception('Не корректный формат ответа от Suggestions');
        }
        $cityData = reset($decoded['data']['items']);
        if (empty($cityData['redirectParams']['geoId'])) {
            throw new \Exception('Пустой geoId при получении ответа');
        }

        return $cityData['redirectParams']['geoId'];
    }
}
