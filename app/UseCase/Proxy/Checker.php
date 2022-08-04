<?php

namespace App\UseCase\Proxy;

use App\Models\City;
use App\Models\Proxy;
use App\UseCase\Search\Params;
use App\UseCase\Search\SearchFactory;
use App\UseCase\Search\SearchSourceInterface;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Console\Helper\ProgressBar;

class Checker
{
    public const PROXY_COUNT_PER_REQUEST = 500;

    public function __construct(private Proxy $proxy)
    {
    }

    public function check(string $searchSourceCode, string $proxySource = null, \Illuminate\Console\OutputStyle $output): void
    {
        $proxyForChecker = $this->proxy->forChecker($searchSourceCode, $proxySource);

        if (empty($proxyForChecker)) {
            return;
        }

        $searchSource = SearchFactory::makeSearchBySourceName($searchSourceCode);

        $proxyForCheckerChunked = array_chunk($proxyForChecker, self::PROXY_COUNT_PER_REQUEST);
        $bar = $output->createProgressBar(count($proxyForCheckerChunked));

        $this->checkChunk(
            $proxyForCheckerChunked,
            new Client(['base_uri' => $searchSource->getUrl()]),
            $searchSource,
            $searchSourceCode,
            $bar
        );

        $bar->finish();
    }

    private function checkChunk(
        array $proxyForCheckerChunked,
        Client $client,
        SearchSourceInterface $searchSource,
        string $searchSourceCode,
        ProgressBar $bar
    ): void {
        $options = $this->setSearchParams($searchSource)->getOptions();
        foreach($proxyForCheckerChunked as $chunk){
            $bar->advance();
            $requestArr = [];
            /** @var Proxy $proxy */
            foreach($chunk as $proxy) {
                $options[RequestOptions::PROXY] = $proxy->address;
                $requestArr[$proxy->address] = $client->getAsync(
                    $searchSource->getUrl(),
                    $options
                );
            }

            $responses = \GuzzleHttp\Promise\settle($requestArr)->wait();
            unset($requestArr);

            foreach ($responses as $proxyAddress => $item) {
                $proxyModel = Proxy::where('address', $proxyAddress)->firstOrFail();

                if (
                    isset($item['value'])
                    && $item['value'] instanceof ResponseInterface
                    && $item['value']->getStatusCode() === 200
                    && ($content = json_decode($item['value']->getBody()->getContents(), true))
                    && $searchSource->isValidResponse($content)
                ) {
                    $proxyModel->{$searchSourceCode} = '1';
                } else {
                    $proxyModel->{$searchSourceCode} = '0';
                }
                $proxyModel->save();
                unset($content);
                unset($proxyModel);
            }
            unset($responses);
        }
    }

    private function setSearchParams(SearchSourceInterface $searchSource): SearchSourceInterface
    {
        $params = new Params();
        $params->setAdults(2);

        /* @var City $city */
        $city = City::query()->whereIn(
            'name',
            [
                'Москва',
                'Санкт-Петербург',
            ]
        )->inRandomOrder()->firstOrFail();
        $params->setCity($city);
        $params->setCheckInDate(Carbon::today()->addDays(rand(1, 100)));
        $checkInDate = clone $params->getCheckInDate();
        $params->setCheckOutDate($checkInDate->addDays(rand(7, 10)));
        $searchSource->setParams($params);

        return $searchSource;
    }
}
