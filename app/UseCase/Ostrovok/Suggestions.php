<?php

namespace App\UseCase\Ostrovok;

use GuzzleHttp\Client;

class Suggestions
{
    public const BASE_URL_PATTERN = 'https://ostrovok.ru/api/site/multicomplete.json?query={query}&locale=ru';

    public function __construct(private Client $client)
    {
    }

    public function findByCityName(string $cityName)
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
        if (!isset($decoded['regions'])) {
            throw new \Exception('Не удалось найти города в Ostrovok');
        }

        $cityId = null;
        foreach ($decoded['regions'] as $region) {
            if ($region['type'] == 'City') {
                $cityId = $region['id'];
                break;
            }
        }

        if (empty($cityId)) {
            throw new \Exception('Пустой cityId');
        }

        return $cityId;
    }
}
