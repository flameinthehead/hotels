<?php

namespace App\UseCase\Proxy\Source;

use App\UseCase\Proxy\SourceInterface;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;

class BestProxies implements SourceInterface
{
    public const SOURCE = 'bestproxies';
    public const BASE_URL = 'https://best-proxies.ru/proxylist/free/';

    public function __construct(private Client $client)
    {
    }

    public function parse(): Collection
    {
        $response = $this->client->request('GET', self::BASE_URL);
        dd($response);
    }

    public function getSource(): string
    {
        return self::SOURCE;
    }

}
