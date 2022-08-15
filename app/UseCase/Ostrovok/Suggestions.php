<?php

namespace App\UseCase\Ostrovok;

use App\Models\Proxy;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;

class Suggestions
{
    public const BASE_URL_PATTERN = 'https://ostrovok.ru/api/site/multicomplete.json?query={query}&locale=ru';
    public const TIMEOUT = 10;

    public function __construct(private Client $client, private Proxy $proxy)
    {
    }

    public function findByCityName(string $cityName): int
    {
        $proxyList = $this->proxy->getAllEnabledBySource('ostrovok');

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
            if (!isset($decoded['regions'])) {
                continue;
            }

            foreach ($decoded['regions'] as $region) {
                if ($region['type'] == 'City') {
                    return $region['id'];
                }
            }
        }

        throw new \Exception('Ostrovok - Не корректный формат ответа от Suggestions');
    }
}
