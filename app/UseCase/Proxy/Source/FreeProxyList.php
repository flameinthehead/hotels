<?php

namespace App\UseCase\Proxy\Source;

use App\UseCase\Proxy\SourceInterface;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;

class FreeProxyList implements SourceInterface
{
    public const SOURCE = 'freeproxylist';
    public const BASE_URL = 'https://www.freeproxylists.net/ru/';

    public function __construct(private Client $client)
    {
    }

    public function parse(): Collection
    {
        $response = $this->client->request('GET', self::BASE_URL, [
            ":authority" => "www.freeproxylists.net",
            ":method" => "GET",
            ":path" => "/ru/",
            ":scheme" => "https",
            "accept" => "text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
            "accept-encoding" => "gzip, deflate, br",
            "accept-language" => "ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7",
            "cache-control" => "max-age=0",
            "sec-ch-ua" => '" Not A;Brand";v="99", "Chromium";v="100"',
            "sec-ch-ua-mobile" => "?0",
            "sec-ch-ua-platform" => '"Linux"',
            "sec-fetch-dest" => "document",
            "sec-fetch-mode" => "navigate",
            "sec-fetch-site" => "none",
            "sec-fetch-user" => "?1",
            "upgrade-insecure-requests" => "1",
            "user-agent" => "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/100.0.4896.60 Safari/537.36",
        ]);
        $html = $response->getBody()->getContents();
        dd($html);
    }

    public function getSource(): string
    {
        return self::SOURCE;
    }
}
