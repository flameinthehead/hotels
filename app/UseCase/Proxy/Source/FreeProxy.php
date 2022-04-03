<?php

namespace App\UseCase\Proxy\Source;

use App\UseCase\Proxy\SourceInterface;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;


class FreeProxy implements SourceInterface
{
    public const BASE_URL = 'http://free-proxy.cz/ru/proxylist/main/ping/';
    public const SOURCE = 'freeproxy';

    public function __construct(private Client $client)
    {
    }

    public function parse(): Collection
    {
        $proxyList = [];
        // первые 20 страниц по пингу
        for($page = 1; $page <= 20; ++$page){
            $response = $this->client->request('GET', self::BASE_URL . $page, [
                'connect_timeout' => 10,
            ]);
            $html = $response->getBody()->getContents();
            dd($html);
            preg_match('/<table id="proxy_list">(.*)</table>/', $html, $matches);
        }
        return collect($proxyList);
    }

    public function getSource(): string
    {
        return self::SOURCE;
    }
}
