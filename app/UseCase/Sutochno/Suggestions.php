<?php

namespace App\UseCase\Sutochno;

use App\Models\Proxy;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;

class Suggestions
{
    public const BASE_URL_PATTERN = 'https://sutochno.ru/api/rest/search/getTermSuggestionsWithBoundingBox?query={query}';
    public const TIMEOUT = 5;
    public const CONNECTION_TIMEOUT = 5;

    public function __construct(private Client $client, private Proxy $proxy)
    {
    }

    public function findByCityName(string $cityName): string
    {
        $proxyList = $this->proxy->getAllEnabledBySource('sutochno', 100);
        if (empty($proxyList)) {
            throw new \Exception('Sutochno - Не найдены рабочие прокси');
        }

        $requestArr = [];
        $options = [
            RequestOptions::TIMEOUT => self::TIMEOUT,
            RequestOptions::HEADERS => [
                'token' => 'Hy6U3z61fflbgT2yJ/VdlQ2719',
            ],
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

            if (!isset($decoded['data']['suggestions']) || !is_array($decoded['data']['suggestions'])) {
                continue;
            }

            $suggestion = reset($decoded['data']['suggestions']);
            if (empty($suggestion)) {
                continue;
            }

            $bbox = $suggestion['bbox'];
            if (empty($bbox) || !is_array($bbox)) {
                continue;
            }

            return json_encode($bbox);
        }

        throw new \Exception('Sutochno - Не корректный формат ответа от Suggestions');
    }
}
