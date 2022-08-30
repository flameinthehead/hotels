<?php

namespace App\UseCase\Proxy\Source;

use App\Models\Proxy;
use App\UseCase\Proxy\SourceInterface;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Collection;
use Psr\Http\Message\ResponseInterface;

class Geonode implements SourceInterface
{
    public const SOURCE = 'geonode';
    public const BASE_URL = 'https://proxylist.geonode.com/api/proxy-list?limit=500&page={page}&sort_by=lastChecked&sort_type=desc';

    public function __construct(private Client $client, private Proxy $proxy)
    {
    }

    public function parse(): Collection
    {
        $yandexProxies = $this->proxy->getAllEnabledBySource('yandex', 50);

        $output = [];
        for($page = 1; $page < 50; ++$page) {
            shuffle($yandexProxies);

            $requestArr = [];
            $proxiesCount = 0;
            foreach ($yandexProxies as $proxyModel) {
                $options = [
                    RequestOptions::PROXY => $proxyModel->address,
                    RequestOptions::TIMEOUT => 20
                ];

                $requestArr[$proxyModel->address] = $this->client->getAsync(
                    str_replace('{page}', $page, self::BASE_URL),
                    $options
                );
                ++$proxiesCount;
                if ($proxiesCount > 10) {
                    break;
                }
            }
            unset($proxyList);

            $responses = \GuzzleHttp\Promise\settle($requestArr)->wait();

            foreach ($responses as $response) {
                if (!isset($response['value']) || !($response['value'] instanceof ResponseInterface)) {
                    continue;
                }

                $content = $response['value']->getBody()->getContents();

                if (empty($content)) {
                    continue;
                }

                $json = json_decode($content, true);


                if (empty($json) || !is_array($json) || !isset($json['data'])) {
                    continue;
                }

                break;
            }
            if (empty($json['data'])) {
                break;
            }

            foreach ($json['data'] as $item) {
                if (!empty($item['ip']) && !empty($item['port'])) {
                    $output[] = [
                        'address' => $item['ip'].':'.$item['port'],
                        'source' => $this->getSource(),
                    ];
                }
            }
        }

        return collect($output);
    }

    public function getSource(): string
    {
        return self::SOURCE;
    }

}
